<?php


namespace Pars\Core\Database\Updater;


use Pars\Model\Authorization\Permission\PermissionBeanFinder;
use Pars\Model\Authorization\Role\RoleBeanFinder;
use Pars\Model\Authorization\Role\RoleBeanProcessor;
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
        $rolePermissionFinder->setUserRole_ID($role->get('UserRole_ID'));
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
        return 'New: ' . implode(', ', $rolePermissionBeanList->column('UserPermission_Code'));
    }


    public function updateRoleModerator()
    {
        return $this->role('moderator', 'Moderator', true);
    }

    public function updateModeratorPermissions()
    {
        $authorPermissions = [
            'content',
            'media',
            'article',
            'article.create',
            'article.edit',
            'article.delete',
            'cmsmenu',
            'cmsmenu.create',
            'cmsmenu.edit',
            'cmsmenu.delete',
            'cmspage',
            'cmspage.create',
            'cmspage.edit',
            'cmspage.delete',
            'cmspageparagraph',
            'cmspageparagraph.create',
            'cmspageparagraph.edit',
            'cmspageparagraph.delete',
            'cmsparagraph',
            'cmsparagraph.create',
            'cmsparagraph.edit',
            'cmsparagraph.delete',
            'cmspost',
            'cmspost.create',
            'cmspost.edit',
            'cmspost.delete',
            'file',
            'file.create',
            'file.edit',
            'file.delete',
            'filedirectory',
            'filedirectory.create',
            'filedirectory.edit',
            'filedirectory.delete',
        ];
        return $this->rolePermissions('moderator', $authorPermissions);
    }

    public function updateRoleAuthor()
    {
        return $this->role('author', 'Author', true);
    }

    public function updateAuthorPermissions()
    {
        $authorPermissions = [
            'content',
            'media',
            'article',
            'article.create',
          #  'article.edit',
          #  'article.delete',
          #  'cmsmenu',
          #  'cmsmenu.create',
          #  'cmsmenu.edit',
          #  'cmsmenu.delete',
            'cmspage',
            'cmspage.create',
          #  'cmspage.edit',
          #  'cmspage.delete',
            'cmspageparagraph',
            'cmspageparagraph.create',
          #  'cmspageparagraph.edit',
          #  'cmspageparagraph.delete',
            'cmsparagraph',
            'cmsparagraph.create',
          #  'cmsparagraph.edit',
          #  'cmsparagraph.delete',
            'cmspost',
            'cmspost.create',
          #  'cmspost.edit',
          #  'cmspost.delete',
            'file',
            'file.create',
          #  'file.edit',
          #  'file.delete',
            'filedirectory',
            'filedirectory.create',
          #  'filedirectory.edit',
          #  'filedirectory.delete',
        ];
        return $this->rolePermissions('author', $authorPermissions);
    }


    public function updateRoleBlogger()
    {
        return $this->role('blogger', 'Blogger', true);
    }

    public function updateBloggerPermissions()
    {
        $authorPermissions = [
            'content',
            #'media',
            'article',
            'article.create',
            #  'article.edit',
            #  'article.delete',
            #  'cmsmenu',
            #  'cmsmenu.create',
            #  'cmsmenu.edit',
            #  'cmsmenu.delete',
            'cmspage',
            #'cmspage.create',
            #  'cmspage.edit',
            #  'cmspage.delete',
            #'cmspageparagraph',
            #'cmspageparagraph.create',
            #  'cmspageparagraph.edit',
            #  'cmspageparagraph.delete',
            #'cmsparagraph',
            #'cmsparagraph.create',
            #  'cmsparagraph.edit',
            #  'cmsparagraph.delete',
            'cmspost',
            'cmspost.create',
            #  'cmspost.edit',
            #  'cmspost.delete',
            #'file',
            #'file.create',
            #  'file.edit',
            #  'file.delete',
            #'filedirectory',
            #'filedirectory.create',
            #  'filedirectory.edit',
            #  'filedirectory.delete',
        ];
        return $this->rolePermissions('blogger', $authorPermissions);
    }

    /**
     * @param string $code
     * @param string $name
     * @param bool $active
     * @return int|string
     */
    protected function role(string $code, string $name, bool $active) {
        $roleFinder = new RoleBeanFinder($this->adapter);
        $roleFinder->setUserRole_Code($code);
        if ($roleFinder->count() === 0) {
            $roleProcessor = new RoleBeanProcessor($this->adapter);
            $roleBean = $roleFinder->getBeanFactory()->getEmptyBean([]);
            $roleBeanList = $roleFinder->getBeanFactory()->getEmptyBeanList();
            $roleBean->set('UserRole_Code', $code);
            $roleBean->set('UserRole_Name', $name);
            $roleBean->set('UserRole_Active', $active);
            $roleBeanList->push($roleBean);
            $roleProcessor->setBeanList($roleBeanList);
            if ($this->getMode() == self::MODE_EXECUTE) {
                $roleProcessor->save();
            }
            return $roleBeanList->count();
        }
        return '';
    }

    /**
     * @param string $roleCode
     * @param array $permissions
     * @return string
     * @throws \Niceshops\Bean\Type\Base\BeanException
     */
    protected function rolePermissions(string $roleCode, array $permissions) {
        $roleFinder = new RoleBeanFinder($this->adapter);
        $roleFinder->setUserRole_Code($roleCode);
        if ($roleFinder->count() == 1) {
            $role = $roleFinder->getBean();
            $permissionFinder = new PermissionBeanFinder($this->adapter);
            $permissionBeanList = $permissionFinder->getBeanList();
            $rolePermissionFinder = new RolePermissionBeanFinder($this->adapter);
            $rolePermissionFinder->setUserRole_ID($role->get('UserRole_ID'));
            $rolePermissionBeanList = $rolePermissionFinder->getBeanFactory()->getEmptyBeanList();
            $rolePermissionBeanListDelete = $rolePermissionFinder->getBeanFactory()->getEmptyBeanList();
            $existingRolerPermissionBeanList = $rolePermissionFinder->getBeanList();
            $existing = $existingRolerPermissionBeanList->column('UserPermission_Code');
            foreach ($permissionBeanList as $permission) {
                $rolePermission = $rolePermissionFinder->getBeanFactory()->getEmptyBean([]);
                $rolePermission->set('UserRole_ID', $role->get('UserRole_ID'));
                $rolePermission->set('UserPermission_Code', $permission->get('UserPermission_Code'));
                if (
                    !in_array($permission->get('UserPermission_Code'), $existing)
                    && in_array($permission->get('UserPermission_Code'), $permissions)
                ) {
                    $rolePermissionBeanList->push($rolePermission);
                } elseif (
                    in_array($permission->get('UserPermission_Code'), $existing)
                    && !in_array($permission->get('UserPermission_Code'), $permissions)
                ) {
                    $rolePermissionBeanListDelete->push($rolePermission);
                }
            }
            $rolePermissionProcessor = new RolePermissionBeanProcessor($this->adapter);
            $rolePermissionProcessor->setBeanList($rolePermissionBeanList);
            if ($this->getMode() == self::MODE_EXECUTE) {
                $rolePermissionProcessor->save();
            }
            $rolePermissionProcessor = new RolePermissionBeanProcessor($this->adapter);
            $rolePermissionProcessor->setBeanList($rolePermissionBeanListDelete);
            if ($this->getMode() == self::MODE_EXECUTE) {
                $rolePermissionProcessor->delete();
            }
            return 'New: ' . implode(', ', $rolePermissionBeanList->column('UserPermission_Code'))
                . '<br>Delete: ' . implode(', ', $rolePermissionBeanListDelete->column('UserPermission_Code'));
        }
        return '';
    }

}
