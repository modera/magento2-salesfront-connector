<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Config;

class Vehicle
{
    public const VH_PREFIX = 'Vh_';
    public const VH_ATTRIBUTE_SET_NAME = 'Vehicle';

    /* GROUPS */
    public const VH_GROUP_NAME_ENGINE = self::VH_PREFIX . 'Engine';
    public const VH_GROUP_NAME_PERFORMANCE = self::VH_PREFIX . 'Performance';
    public const VH_GROUP_NAME_TRANSMISSION = self::VH_PREFIX . 'Transmission';
    public const VH_GROUP_NAME_BODY = self::VH_PREFIX . 'Body';
    public const VH_GROUP_NAME_ECONOMY = self::VH_PREFIX . 'Economy';
    public const VH_GROUP_NAME_IDENTIFICATION = self::VH_PREFIX . 'Identification';
    public const VH_GROUP_NAME_PRICE = self::VH_PREFIX . 'Price';

    /* ENGINE ATTRIBUTES*/
    public const VH_ATTR_ENGINE_FUEL = 'vh_engine_fuel';
    public const VH_ATTR_ENGINE_FUEL_LABEL = 'Fuel';
    public const VH_ATTR_ENGINE_TYPE = 'vh_engine_type';
    public const VH_ATTR_ENGINE_TYPE_LABEL = 'Engine type';
    public const VH_ATTR_ENGINE_NAME = 'vh_engine_name';
    public const VH_ATTR_ENGINE_NAME_LABEL = 'Engine Description';
    public const VH_ATTR_ENGINE_CYLINDERS = 'vh_engine_cylinders';
    public const VH_ATTR_ENGINE_CYLINDERS_LABEL = 'Number of cylinders';
    public const VH_ATTR_ENGINE_CYLINDERS_CAPACITY_CM3 = 'vh_engine_cylinders_cm';
    public const VH_ATTR_ENGINE_CYLINDERS_CAPACITY_CM3_LABEL = 'Cylinder capacity (cm3)';
    public const VH_ATTR_ENGINE_CYLINDERS_CAPACITY_L = 'vh_engine_cylinders_litre';
    public const VH_ATTR_ENGINE_CYLINDERS_CAPACITY_L_LABEL = 'Cylinder capacity (L)';
    public const VH_ATTR_ENGINE_COMMENTS = 'vh_engine_comments';
    public const VH_ATTR_ENGINE_COMMENTS_LABEL = 'Notes';

    /* PERFORMANCE ATTRIBUTES*/
    public const VH_ATTR_PERFORMANCE_MAX_POWER_KW = 'vh_performance_max_power_kw';
    public const VH_ATTR_PERFORMANCE_MAX_POWER_KW_LABEL = 'Max power (kW)';
    public const VH_ATTR_PERFORMANCE_MAX_POWER_RPM = 'vh_performance_max_power_rpm';
    public const VH_ATTR_PERFORMANCE_MAX_POWER_RPM_LABEL = 'Max power (rpm)';
    public const VH_ATTR_PERFORMANCE_MAX_TORQUE = 'vh_performance_max_torque';
    public const VH_ATTR_PERFORMANCE_MAX_TORQUE_LABEL = 'Max torque (Nm)';
    public const VH_ATTR_PERFORMANCE_MAX_SPEED = 'vh_performance_max_speed';
    public const VH_ATTR_PERFORMANCE_MAX_SPEED_LABEL = 'Max speed (km/h)';

    /* TRANSMISSION ATTRIBUTES*/
    public const VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE = 'vh_transmission_drivetrain';
    public const VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE_LABEL = 'Driving wheels';
    public const VH_ATTR_TRANSMISSION_TYPE = 'vh_transmission_type';
    public const VH_ATTR_TRANSMISSION_TYPE_LABEL = 'Gearbox type';
    public const VH_ATTR_TRANSMISSION_NAME = 'vh_transmission_name';
    public const VH_ATTR_TRANSMISSION_NAME_LABEL = 'Gearbox description';
    public const VH_ATTR_TRANSMISSION_GEARS = 'vh_transmission_gears';
    public const VH_ATTR_TRANSMISSION_GEARS_LABEL = 'Number of gears';

    /* BODY ATTRIBUTES*/
    public const VH_ATTR_BODY_TYPE = 'vh_body_type';
    public const VH_ATTR_BODY_TYPE_LABEL = 'Body type';
    public const VH_ATTR_BODY_TYPE_NAME = 'vh_body_type_name';
    public const VH_ATTR_BODY_TYPE_NAME_LABEL = 'Body description';
    public const VH_ATTR_BODY_COLOR = 'vh_body_color';
    public const VH_ATTR_BODY_COLOR_LABEL = 'Color type';
    public const VH_ATTR_BODY_COLOR_NAME = 'vh_body_color_name';
    public const VH_ATTR_BODY_COLOR_NAME_LABEL = 'Color description';
    public const VH_ATTR_BODY_DOORS = 'vh_body_doors';
    public const VH_ATTR_BODY_DOORS_LABEL = 'Number of doors';
    public const VH_ATTR_BODY_SEATS = 'vh_body_seats';
    public const VH_ATTR_BODY_SEATS_LABEL = 'Number of seats';
    public const VH_ATTR_BODY_TRUNC_SIZE_L = 'vh_body_trunc_size';
    public const VH_ATTR_BODY_TRUNC_SIZE_L_LABEL = 'Trunk size (L)';
    public const VH_ATTR_BODY_FUEL_TANK_SIZE = 'vh_body_fuel_tank_size';
    public const VH_ATTR_BODY_FUEL_TANK_SIZE_LABEL = 'Fuel tank size (L)';
    public const VH_ATTR_BODY_WEIGHT = 'vh_body_weight';
    public const VH_ATTR_BODY_WEIGHT_LABEL = 'Empty weight (kg)';
    public const VH_ATTR_BODY_MAX_WEIGHT = 'vh_body_max_weight';
    public const VH_ATTR_BODY_MAX_WEIGHT_LABEL = 'Max weight (kg)';
    public const VH_ATTR_BODY_LENGTH = 'vh_body_length';
    public const VH_ATTR_BODY_LENGTH_LABEL = 'Length (mm)';
    public const VH_ATTR_BODY_WIDTH = 'vh_body_width';
    public const VH_ATTR_BODY_WIDTH_LABEL = 'Width (mm)';
    public const VH_ATTR_BODY_HEIGHT = 'vh_body_height';
    public const VH_ATTR_BODY_HEIGHT_LABEL = 'Height (mm)';
    public const VH_ATTR_BODY_WHEELBASE = 'vh_body_wheelbase';
    public const VH_ATTR_BODY_WHEELBASE_LABEL = 'Wheelbase (mm)';

    /* ECONOMY ATTRIBUTES*/
    public const VH_ATTR_ECONOMY_EURO_CLASS = 'vh_economy_euro_class';
    public const VH_ATTR_ECONOMY_EURO_CLASS_LABEL = 'Euro class';
    public const VH_ATTR_ECONOMY_CO2_EMISSION = 'vh_economy_co2_emission';
    public const VH_ATTR_ECONOMY_CO2_EMISSION_LABEL = 'CO2 emissions (g/kg)';
    public const VH_ATTR_ECONOMY_STANDART = 'vh_economy_standart';
    public const VH_ATTR_ECONOMY_STANDART_LABEL = 'Economy standard';
    public const VH_ATTR_ECONOMY_CITY = 'vh_economy_city';
    public const VH_ATTR_ECONOMY_CITY_LABEL = 'Fuel economy city (l/100km)';
    public const VH_ATTR_ECONOMY_HIGHWAY = 'vh_economy_highway';
    public const VH_ATTR_ECONOMY_HIGHWAY_LABEL = 'Fuel economy highway (l/100km)';
    public const VH_ATTR_ECONOMY_COMBINED = 'vh_economy_combined';
    public const VH_ATTR_ECONOMY_COMBINED_LABEL = 'Fuel economy combined (l/100km)';
    public const VH_ATTR_ECONOMY_RANGE = 'vh_economy_range';
    public const VH_ATTR_ECONOMY_RANGE_LABEL = 'Range (km)';

    /* IDENTIFICATION ATTRIBUTES*/
    public const VH_ATTR_IDENTIFICATION_MAKE = 'vh_identification_make';
    public const VH_ATTR_IDENTIFICATION_MAKE_LABEL = 'Make';
    public const VH_ATTR_IDENTIFICATION_MODEL = 'vh_identification_model';
    public const VH_ATTR_IDENTIFICATION_MODEL_LABEL = 'Model';
    public const VH_ATTR_IDENTIFICATION_MODEL_TYPE = 'vh_identification_model_type';
    public const VH_ATTR_IDENTIFICATION_MODEL_TYPE_LABEL = 'Model Type';
    public const VH_ATTR_IDENTIFICATION_MODEL_YEAR = 'vh_identification_model_year';
    public const VH_ATTR_IDENTIFICATION_MODEL_YEAR_LABEL = 'Model Year';
    public const VH_ATTR_IDENTIFICATION_MODEL_CODE = 'vh_identification_model_code';
    public const VH_ATTR_IDENTIFICATION_MODEL_CODE_LABEL = 'Model factory code';
    public const VH_ATTR_IDENTIFICATION_REG_NR = 'vh_identification_reg_nr';
    public const VH_ATTR_IDENTIFICATION_REG_NR_LABEL = 'Registration plate';
    public const VH_ATTR_IDENTIFICATION_VIN_CODE = 'vh_identification_vin_code';
    public const VH_ATTR_IDENTIFICATION_VIN_CODE_LABEL = 'VIN';
    public const VH_ATTR_IDENTIFICATION_MILLEAGE = 'vh_identification_milleage';
    public const VH_ATTR_IDENTIFICATION_MILLEAGE_LABEL = 'Mileage';
    public const VH_ATTR_IDENTIFICATION_ORDER_NUMBER = 'vh_identification_order_number';
    public const VH_ATTR_IDENTIFICATION_ORDER_NUMBER_LABEL = 'Order number';
    public const VH_ATTR_IDENTIFICATION_TITLE = 'vh_identification_title';
    public const VH_ATTR_IDENTIFICATION_TITLE_LABEL = 'Description';
    public const VH_ATTR_IDENTIFICATION_HASH = 'vh_identification_hash';
    public const VH_ATTR_IDENTIFICATION_HASH_LABEL = 'Hash';
    public const VH_ATTR_IDENTIFICATION_FIRST_REGISTRATION = 'vh_identification_first_registration';
    public const VH_ATTR_IDENTIFICATION_FIRST_REGISTRATION_LABEL = 'First registration';
    public const VH_ATTR_IDENTIFICATION_EQUIPMENT = 'vh_identification_equipment';
    public const VH_ATTR_IDENTIFICATION_EQUIPMENT_LABEL = 'Equipment';
    public const VH_ATTR_IDENTIFICATION_EQUIPMENT_SPECIAL = 'vh_identification_equipment_special';
    public const VH_ATTR_IDENTIFICATION_EQUIPMENT_SPECIAL_LABEL = 'Special Equipment';
    public const VH_ATTR_IDENTIFICATION_EQUIPMENT_EXTRA = 'vh_identification_equipment_extra';
    public const VH_ATTR_IDENTIFICATION_EQUIPMENT_EXTRA_LABEL = 'Extra Equipment';
    public const VH_ATTR_IDENTIFICATION_GALLERY = 'vh_identification_gallery';
    public const VH_ATTR_IDENTIFICATION_GALLERY_LABEL = 'Galery';
    public const VH_ATTR_IDENTIFICATION_TAGS = 'vh_identification_tags';
    public const VH_ATTR_IDENTIFICATION_TAGS_LABEL = 'Tags';
    public const VH_ATTR_IDENTIFICATION_BOOKED_UNTIL = 'vh_identification_booked_until';
    public const VH_ATTR_IDENTIFICATION_BOOKED_UNTIL_LABEL = 'Booked Until';
    public const VH_ATTR_IDENTIFICATION_CONDITION = 'vh_identification_condition';
    public const VH_ATTR_IDENTIFICATION_CONDITION_LABEL = 'Condition';
}
