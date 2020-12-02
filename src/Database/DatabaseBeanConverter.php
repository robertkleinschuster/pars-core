<?php

namespace Pars\Core\Database;

use Niceshops\Bean\Converter\AbstractBeanConverter;
use Niceshops\Bean\Type\Base\AbstractBaseBean;
use Niceshops\Bean\Type\Base\BeanException;
use Niceshops\Bean\Type\Base\BeanInterface;
use Pars\Model\Article\ArticleDataBean;

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
                return (string) $value;
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
        throw new \Exception("Unable to convert $name to db.");
    }

    public function convertValueToBean(BeanInterface $bean, string $name, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        switch ($bean->type($name)) {
            case AbstractBaseBean::DATA_TYPE_STRING:
                return (string) $value;
            case AbstractBaseBean::DATA_TYPE_BOOL:
                if ($value == 1) {
                    return true;
                } elseif ($value == 0) {
                    return false;
                }
                break;
            case AbstractBaseBean::DATA_TYPE_INT:
                return (int) $value;
            case AbstractBaseBean::DATA_TYPE_FLOAT:
                return (bool) $value;
            case AbstractBaseBean::DATA_TYPE_ARRAY:
                return json_decode($value);
            case \DateTime::class:
                return \DateTime::createFromFormat(self::DATE_FORMAT, $value);
            case BeanInterface::class:
                if (is_string($value)) {
                    try {
                        $decoded = json_decode($value, true);
                        if ($decoded) {
                            return AbstractBaseBean::createFromArray($decoded);
                        }
                    } catch (BeanException $exception) {
                        throw new \Exception("Unable to convert $name from db.", 0, $exception);
                    }
                }
        }
        throw new \Exception("Unable to convert $name from db.");
    }
}
