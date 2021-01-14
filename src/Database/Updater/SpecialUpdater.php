<?php


namespace Pars\Core\Database\Updater;


use Pars\Model\Authorization\Permission\PermissionBeanFinder;
use Pars\Model\Authorization\Role\RoleBeanFinder;
use Pars\Model\Authorization\RolePermission\RolePermissionBeanFinder;
use Pars\Model\Authorization\RolePermission\RolePermissionBeanProcessor;
use Pars\Model\Config\ConfigBeanFinder;
use Pars\Model\Config\ConfigBeanProcessor;

class SpecialUpdater extends AbstractUpdater
{
    public function getCode(): string
    {
        return 'special';
    }

    public function updateVersion()
    {
        $output = [];
        exec('pwd', $output);
        exec('git fetch --dry-run', $output);
        if ($this->getMode() == self::MODE_EXECUTE) {
           exec('git pull', $output);
           exec('composer update', $output);
           $processor = new ConfigBeanProcessor($this->adapter);
           $processor->force = true;
           $finder = new ConfigBeanFinder($this->adapter);
           $list = $finder->getBeanFactory()->getEmptyBeanList();
           $bean = $finder->getBeanFactory()->getEmptyBean([]);
           $bean->set('Config_Code', 'frontend.update');
           $bean->set('Config_Value', 'true');
           $bean->set('Config_Locked', true);
           $list->push($bean);
           $processor->setBeanList($list);
           $processor->save();
        }
        return implode('<br>', $output);
    }


    public function updateAdminPermissions()
    {
        $roleFinder = new RoleBeanFinder($this->adapter);
        $roleFinder->setUserRole_Code('admin');
        $role = $roleFinder->getBean();
        $permissionFinder = new PermissionBeanFinder($this->adapter);
        $permissionBeanList = $permissionFinder->getBeanList();
        $rolePermissionFinder = new RolePermissionBeanFinder($this->adapter);
        $rolePermissionBeanList = $rolePermissionFinder->getBeanFactory()->getEmptyBeanList();
        $existingRolerPermissionBeanList = $rolePermissionFinder->getBeanList();
        $existing = $existingRolerPermissionBeanList->column('UserPermission_Code');
        foreach ($permissionBeanList as $permission) {
            if (!in_array($permission->get('UserPermission_Code'), $existing)) {
                $rolePermission = $rolePermissionFinder->getBeanFactory()->getEmptyBean([]);
                $rolePermission->set('UserRole_ID', $role->get('UserRole_ID'));
                $rolePermission->set('UserPermission_Code', $permission->get('UserPermission_Code'));
                $rolePermissionBeanList->push($rolePermission);
            }
        }

        $rolePermissionProcessor = new RolePermissionBeanProcessor($this->adapter);
        $rolePermissionProcessor->setBeanList($rolePermissionBeanList);
        if ($this->getMode() == self::MODE_EXECUTE) {
            $rolePermissionProcessor->save();
        }
        return 'New Permissions: ' . $rolePermissionBeanList->count();
    }


    public function updateLockedConfig() {
        $finder = new ConfigBeanFinder($this->adapter);
        $finder->setConfig_Code('asset.key');
        $beanList = $finder->getBeanList();
        foreach ($beanList as $bean) {
            $bean->set('Config_Locked', true);
        }

        $processor = new ConfigBeanProcessor($this->adapter);
        $processor->force = true;
        $processor->setBeanList($beanList);
        if ($this->getMode() == self::MODE_EXECUTE) {
            return $processor->save();
        }
    }
}
