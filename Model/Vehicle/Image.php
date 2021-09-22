<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Vehicle;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Catalog\Model\Product\Gallery\Processor;

class Image
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $imageFile;

    /**
     * @var ProductRepository $productRepository
     */
    private $productRepository;

    /**
     * @var Processor
     */
    private $imageProcessor;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param DirectoryList $directoryList
     * @param File $imageFile
     * @param Processor $imageProcessor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DirectoryList $directoryList,
        File $imageFile,
        Processor $imageProcessor
    ) {
        $this->productRepository = $productRepository;
        $this->directoryList = $directoryList;
        $this->imageFile = $imageFile;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Update product images
     *
     * @param ProductInterface $product
     * @param array $imageListBeforeUpdate
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @throws FileSystemException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function updateMedia(ProductInterface $product, array $imageListBeforeUpdate = []): ProductInterface
    {
        $currentProductImageUrls = json_decode($product->getVhIdentificationGallery() ?: '');
        $tmpDir = $this->getMediaDirTmpDir();
        $this->imageFile->checkAndCreateFolder($tmpDir);

        if ($currentProductImageUrls !== $imageListBeforeUpdate && is_array($currentProductImageUrls)) {
            $product = $this->removeGalleryImages($product);
            $isFirstImageSet = true;
            foreach ($currentProductImageUrls as $imageToUpload) {
                $imageInfo = $this->imageFile->getPathInfo($imageToUpload);
                $newFileName = $tmpDir . DIRECTORY_SEPARATOR . $imageInfo['basename'];
                $result = $this->imageFile->read($imageToUpload, $newFileName);
                if ($result
                    && (
                        !isset($imageInfo['extension'])
                        || !in_array(strtolower($imageInfo['extension']), ['jpg', 'jpeg', 'gif', 'png'])
                    )
                ) {
                    if ($ext = $this->getImageType($newFileName)) {
                        $newFileNameWithExt = $newFileName . $ext;
                        $this->imageFile->mv($newFileName, $newFileNameWithExt);
                        $product->addImageToMediaGallery(
                            $newFileNameWithExt,
                            $isFirstImageSet ? ['image', 'small_image', 'thumbnail'] : [],
                            true,
                            false
                        );
                        $isFirstImageSet = false;
                    }
                }
            }
        }

        return $this->productRepository->save($product, true);
    }

    /**
     * Retrieve image extension
     *
     * @param string $fileName
     * @return false|string
     */
    private function getImageType(string $fileName)
    {
        $imageType = exif_imagetype($fileName);
        $ext = false;
        switch ($imageType) {
            case 1:
                $ext = '.gif';
                break;
            case 2:
                $ext = '.jpeg';
                break;
            case 3:
                $ext = '.png';
                break;
        }

        return $ext;
    }

    /**
     * Retrieve media temp directory path
     *
     * @return string
     * @throws FileSystemException
     */
    private function getMediaDirTmpDir(): string
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }

    /**
     * Clear product gallery
     *
     * @param ProductInterface $product
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    private function removeGalleryImages(ProductInterface $product): ProductInterface
    {
        $productGalleryImages = $product->getMediaGalleryImages();
        foreach ($productGalleryImages as $image) {
            $this->imageProcessor->removeImage($product, $image->getFile());
        }

        return $this->productRepository->save($product, true);
    }
}
