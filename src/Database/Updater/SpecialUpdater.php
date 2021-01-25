<?php


namespace Pars\Core\Database\Updater;


use Pars\Model\Authorization\Permission\PermissionBeanFinder;
use Pars\Model\Authorization\Role\RoleBeanFinder;
use Pars\Model\Authorization\RolePermission\RolePermissionBeanFinder;
use Pars\Model\Authorization\RolePermission\RolePermissionBeanProcessor;

class SpecialUpdater extends AbstractUpdater
{
    public function getCode(): string
    {
        return 'special';
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
}
