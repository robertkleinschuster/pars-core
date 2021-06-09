<?php

namespace Pars\Core\Database;

use Pars\Bean\Converter\AbstractBeanConverter;
use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Bean\Type\Base\BeanException;
use Pars\Bean\Type\Base\BeanInterface;

class DatabaseBeanConverter extends AbstractBeanConverter
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function convertValueFromBean(BeanInterface $bean, string $name, $value)
    {
        if ($value === null) {
            return null;
        }
        switch ($bean->type($name)) {
            case AbstractBaseBean::DATA_TYPE_FLOAT:
            case AbstractBaseBean::DATA_TYPE_INT:
            case AbstractBaseBean::DATA_TYPE_STRING:
                return (string)$value;
            case AbstractBaseBean::DATA_TYPE_BOOL:
                if ($value) {
                    return 1;
                } else {
                    return 0;
                }
            case AbstractBaseBean::DATA_TYPE_ARRAY:
                return json_encode($value);
            case \DateTime::class:
                if ($value instanceof \DateTime) {
                    return $value->format(self::DATE_FORMAT);
                }
                break;
            case BeanInterface::class:
                return json_encode($value);
        }
        throw new \Exception("Unable to convert $name to db. Type: " . $bean->type($name));
    }

    public function convertValueToBean(BeanInterface $bean, string $name, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        switch ($bean->type($name)) {
            case AbstractBaseBean::DATA_TYPE_STRING:
                return (string)$value;
            case AbstractBaseBean::DATA_TYPE_BOOL:
                if ($value == 1) {
                    return true;
                } elseif ($value == 0) {
                    return false;
                }
                break;
            case AbstractBaseBean::DATA_TYPE_INT:
                return (int)$value;
            case AbstractBaseBean::DATA_TYPE_FLOAT:
                return (bool)$value;
            case AbstractBaseBean::DATA_TYPE_ARRAY:
                return (array) json_decode($value, true);
            case \DateTime::class:
                return \DateTime::createFromFormat(self::DATE_FORMAT, $value);
            case BeanInterface::class:
                if (is_string($value)) {
                    try {
                        $decoded = (array) json_decode($value, true);
                        if ($decoded) {
                            return AbstractBaseBean::createFromArray($decoded);
                        } else {
                            return null;
                        }
                    } catch (BeanException $exception) {
                        return null;
                    }
                } else {
                    return null;
                }
        }
        throw new \Exception("Unable to convert $name from db. Type: " . $bean->type($name));
    }
}
