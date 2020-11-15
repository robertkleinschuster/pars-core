<?php

namespace Pars\Core\Database;

use Niceshops\Bean\Converter\AbstractBeanConverter;
use Niceshops\Bean\Type\Base\AbstractBaseBean;
use Niceshops\Bean\Type\Base\BeanInterface;

class DatabaseBeanConverter extends AbstractBeanConverter
{
    public function convertValueFromBean(BeanInterface $bean, string $name, $value)
    {
        if ($value === null) {
            return null;
        }
        switch ($bean->getType($name)) {
            case AbstractBaseBean::DATA_TYPE_FLOAT:
            case AbstractBaseBean::DATA_TYPE_INT:
            case AbstractBaseBean::DATA_TYPE_STRING:
                return strval($value);
            case AbstractBaseBean::DATA_TYPE_BOOL:
                if ($value) {
                    return 1;
                } else {
                    return 0;
                }
            case AbstractBaseBean::DATA_TYPE_ARRAY:
                return json_encode($value);
            case AbstractBaseBean::DATA_TYPE_DATETIME:
                if ($value instanceof \DateTime) {
                    return $value->format('Y-m-d H:i:s');
                }
        }
        throw new \Exception("Unable to convert $name to db.");
    }

    public function convertValueToBean(BeanInterface $bean, string $name, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        switch ($bean->getType($name)) {
            case AbstractBaseBean::DATA_TYPE_STRING:
                return strval($value);
            case AbstractBaseBean::DATA_TYPE_BOOL:
                if ($value == 1) {
                    return true;
                } elseif ($value == 0) {
                    return false;
                }
                break;
            case AbstractBaseBean::DATA_TYPE_INT:
                return intval($value);
            case AbstractBaseBean::DATA_TYPE_FLOAT:
                return boolval($value);
            case AbstractBaseBean::DATA_TYPE_ARRAY:
                return json_decode($value);
            case AbstractBaseBean::DATA_TYPE_DATETIME:
                return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        }
        throw new \Exception("Unable to convert $name from db.");
    }
}
