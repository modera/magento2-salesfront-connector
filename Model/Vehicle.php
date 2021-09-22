<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response;
use Magento\Framework\Webapi\Exception as WebApiException;
use Modera\SalesfrontConnector\Api\VehicleInterface;
use Magento\Framework\Webapi\Response as WebapiResponse;
use Modera\SalesfrontConnector\Model\Vehicle\ProductResolver as VehicleProductResolver;
use Modera\SalesfrontConnector\Model\Vehicle\Attribute as VehicleAttribute;
use Modera\SalesfrontConnector\Model\Vehicle\Image as VehicleImage;
use Modera\SalesfrontConnector\Model\Vehicle\Inventory as VehicleInventory;
use Modera\SalesfrontConnector\Model\Vehicle\Category as VehicleCategory;

class Vehicle implements VehicleInterface
{
    /**
     * @var ProductRepository $productRepository
     */
    private $productRepository;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var JwtManagement
     */
    private $jwtManagement;

    /**
     * @var VehicleProductResolver
     */
    private $vehicleProductResolver;

    /**
     * @var VehicleAttribute
     */
    private $vehicleAttribute;

    /**
     * @var VehicleImage
     */
    private $vehicleImage;

    /**
     * @var VehicleInventory
     */
    private $vehicleInventory;

    /**
     * @var VehicleCategory
     */
    private $vehicleCategory;

    /**
     * @param Request $request
     * @param ProductRepositoryInterface $productRepository
     * @param Response $response
     * @param JwtManagement $jwtManagement
     * @param VehicleProductResolver $vehicleProductResolver
     * @param VehicleAttribute $vehicleAttribute
     * @param VehicleImage $vehicleImage
     * @param VehicleInventory $vehicleInventory
     * @param VehicleCategory $vehicleCategory
     */
    public function __construct(
        Request $request,
        ProductRepositoryInterface $productRepository,
        Response $response,
        JwtManagement $jwtManagement,
        VehicleProductResolver $vehicleProductResolver,
        VehicleAttribute $vehicleAttribute,
        VehicleImage $vehicleImage,
        VehicleInventory $vehicleInventory,
        VehicleCategory $vehicleCategory
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->response = $response;
        $this->jwtManagement = $jwtManagement;
        $this->vehicleProductResolver = $vehicleProductResolver;
        $this->vehicleAttribute = $vehicleAttribute;
        $this->vehicleImage = $vehicleImage;
        $this->vehicleInventory = $vehicleInventory;
        $this->vehicleCategory = $vehicleCategory;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function createVehicle()
    {
        $errors = [];

        if (!$this->jwtManagement->validate($this->getJwt())) {
            return $this->response->setHttpResponseCode(WebApiException::HTTP_UNAUTHORIZED)
                ->setContent(json_encode(['error' => 'Unauthorized']))
                ->sendResponse();
        }

        $productData = json_decode($this->request->getContent());
        if (!empty($productData) && property_exists($productData, 'vinCode')) {
            $product = $this->vehicleProductResolver->getProduct($productData->vinCode);
            $productImages = json_decode($product->getVhIdentificationGallery() ?: '') ?: [];
            $product = $this->vehicleAttribute->save($productData, $product, $errors);
            $product = $this->vehicleImage->updateMedia($product, $productImages);
            $this->vehicleInventory->updateInventory($product);
            $this->vehicleCategory->setCategory($product);

            return $this->response->setHttpResponseCode(WebapiResponse::HTTP_OK)
                ->setContent(
                    json_encode(
                        [
                        'id' => $product->getId(),
                        'errors' => $errors
                        ]
                    )
                )
                ->sendResponse();
        }

        return $this->response->setHttpResponseCode(WebApiException::HTTP_BAD_REQUEST)
            ->setContent(
                json_encode(
                    [
                    'errors' => 'Undefined product data'
                    ]
                )
            )
            ->sendResponse();
    }

    /**
     * Retrieve JWT from request
     *
     * @return string
     */
    private function getJwt(): string
    {
        return str_replace('Bearer ', '', $this->request->getHeader('Authorization'));
    }

    /**
     * @inheritDoc
     */
    public function deleteVehicle()
    {
        $result = false;
        $errors = [];

        if (!$this->jwtManagement->validate($this->getJwt())) {
            return $this->response->setHttpResponseCode(WebApiException::HTTP_UNAUTHORIZED)
                ->setContent(json_encode(['error' => 'Unauthorized']))
                ->sendResponse();
        }

        $productData = json_decode($this->request->getContent());
        if (!empty($productData) && property_exists($productData, 'vinCode')) {
            $product = $this->vehicleProductResolver->getProductBySku($productData->vinCode);
            if (is_object($product) && $product->getId()) {
                try {
                    $this->productRepository->delete($product);
                    $result = true;
                } catch (\Throwable $exception) {
                    $errors[] = $exception->getMessage();
                }
            } else {
                $errors[] = 'Can not find product with SKU = ' . $productData->vinCode;
            }
        } else {
            $errors[] = 'Invalid product data in request';
        }

        return [
            'result' => $result,
            'errors' => $errors
        ];
    }
}
