<?php

namespace Pars\Core\Database\Updater;

use Laminas\Db\Sql\Ddl\Column\Boolean;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Text;
use Laminas\Db\Sql\Ddl\Column\Timestamp;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\ForeignKey;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use Laminas\Db\Sql\Ddl\Index\Index;

class SchemaUpdater extends AbstractUpdater
{

    public function getCode(): string
    {
        return 'schema';
    }


    public function updateTablePerson()
    {
        $table = $this->getTableStatement('Person');
        $personId = new Integer('Person_ID');
        $personId->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, $personId);
        $this->addColumnToTable($table, new Varchar('Person_Firstname', 255));
        $this->addColumnToTable($table, new Varchar('Person_Lastname', 255));
        $this->addConstraintToTable($table, new PrimaryKey('Person_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTablePerson_DropConstraints()
    {
        $table = $this->getTableStatement('Person', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('Person_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'File_ID', 'File', 'File_ID'));
        $this->dropColumnFromTable($table, new Integer('File_ID', true));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTablePerson_AddConstraints()
    {
        $table = $this->getTableStatement('Person', true);
     #   $this->addConstraintToTable($table, new ForeignKey(null, 'File_ID', 'File', 'File_ID'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableConfig()
    {
        $table = $this->getTableStatement('Config');
        $this->addColumnToTable($table, new Varchar('Config_Code', 255));
        $this->addColumnToTable($table, new Varchar('Config_Value', 255, true));
        $this->addColumnToTable($table, new Varchar('Config_Description', 255, true));
        $this->addColumnToTable($table, new Text('Config_Options', 65535, true));
        $this->addColumnToTable($table, new Boolean('Config_Locked', true, 0));
        $this->addColumnToTable($table, new Text('Config_Data', 65535, true));
        $this->addConstraintToTable($table, new PrimaryKey('Config_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableConfig_DropConstraints()
    {
        $table = $this->getTableStatement('Config', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('Config_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableConfig_AddConstraints()
    {
        $table = $this->getTableStatement('Config', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }


    public function updateTableFileType()
    {
        $table = $this->getTableStatement('FileType');
        $this->addColumnToTable($table, new Varchar('FileType_Code', 255));
        $this->addColumnToTable($table, new Varchar('FileType_Mime', 255));
        $this->addColumnToTable($table, new Varchar('FileType_Name', 255));
        $this->addColumnToTable($table, new Boolean('FileType_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('FileType_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableFileType_DropConstraints()
    {
        $table = $this->getTableStatement('FileType', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('FileType_Code'));
        $this->dropConstraintFromTable($table, new UniqueKey('FileType_Mime'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableFileType_AddConstraints()
    {
        $table = $this->getTableStatement('FileType', true);
        $this->addConstraintToTable($table, new UniqueKey('FileType_Mime'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableFileDirectory()
    {
        $table = $this->getTableStatement('FileDirectory');
        $this->addColumnToTable($table, new Integer('FileDirectory_ID'))->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Varchar('FileDirectory_Code', 255));
        $this->addColumnToTable($table, new Varchar('FileDirectory_Name', 255));
        $this->addColumnToTable($table, new Boolean('FileDirectory_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('FileDirectory_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableFileDirectory_DropConstraints()
    {
        $table = $this->getTableStatement('FileDirectory', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('FileDirectory_ID'));
        $this->dropConstraintFromTable($table, new UniqueKey('FileDirectory_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableFileDirectory_AddConstraints()
    {
        $table = $this->getTableStatement('FileDirectory', true);
        $this->addConstraintToTable($table, new UniqueKey('FileDirectory_Code'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableFile()
    {
        $table = $this->getTableStatement('File');
        $this->addColumnToTable($table, new Integer('File_ID'))->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Varchar('FileType_Code', 255));
        $this->addColumnToTable($table, new Integer('FileDirectory_ID', 255));
        $this->addColumnToTable($table, new Varchar('File_Name', 255));
        $this->addColumnToTable($table, new Varchar('File_Code', 255));
        $this->addConstraintToTable($table, new PrimaryKey('File_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableFile_DropConstraints()
    {
        $table = $this->getTableStatement('File', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('File_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'FileType_Code', 'FileType', 'FileType_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'FileDirectory_ID', 'FileDirectory', 'FileDirectory_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new UniqueKey(['File_Code', 'FileDirectory_ID']));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableFile_AddConstraints()
    {
        $table = $this->getTableStatement('File', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'FileType_Code', 'FileType', 'FileType_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'FileDirectory_ID', 'FileDirectory', 'FileDirectory_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new UniqueKey(['File_Code', 'FileDirectory_ID']));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableLocale()
    {
        $table = $this->getTableStatement('Locale');
        $this->addColumnToTable($table, new Varchar('Locale_Code', 255));
        $this->addColumnToTable($table, new Varchar('Locale_UrlCode', 255));
        $this->addColumnToTable($table, new Varchar('Locale_Name', 255));
        $this->addColumnToTable($table, new Boolean('Locale_Active', false, 0));
        $this->addColumnToTable($table, new Integer('Locale_Order', false, 0));
        $this->addConstraintToTable($table, new PrimaryKey('Locale_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableLocale_DropConstraints()
    {
        $table = $this->getTableStatement('Locale', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('Locale_Code'));
        $this->dropConstraintFromTable($table, new UniqueKey('Locale_UrlCode'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableLocale_AddConstraints()
    {
        $table = $this->getTableStatement('Locale', true);
        $this->addConstraintToTable($table, new UniqueKey('Locale_UrlCode'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableUserState()
    {
        $table = $this->getTableStatement('UserState');
        $this->addColumnToTable($table, new Varchar('UserState_Code', 255));
        $this->addColumnToTable($table, new Boolean('UserState_Active', 255));
        $this->addConstraintToTable($table, new PrimaryKey('UserState_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableUserState_DropConstraints()
    {
        $table = $this->getTableStatement('UserState', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('UserState_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableUserState_AddConstraints()
    {
        $table = $this->getTableStatement('UserState', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableUser()
    {
        $table = $this->getTableStatement('User');
        $this->addColumnToTable($table, new Integer('Person_ID'));
        $this->addColumnToTable($table, new Varchar('UserState_Code', 255));
        $this->addColumnToTable($table, new Varchar('User_Username', 255));
        $this->addColumnToTable($table, new Varchar('User_Displayname', 255));
        $this->addColumnToTable($table, new Varchar('User_Password', 255));
        $this->addColumnToTable($table, new Timestamp('User_LastLogin', true));
        $this->addColumnToTable($table, new Varchar('Locale_Code', 255));
        $this->addConstraintToTable($table, new PrimaryKey('Person_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableUser_DropConstraints()
    {
        $table = $this->getTableStatement('User', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('Person_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Person_ID', 'Person', 'Person_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'UserState_Code', 'UserState', 'UserState_Code'));
        $this->dropConstraintFromTable($table, new UniqueKey('User_Username'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Locale_Code', 'Locale', 'Locale_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableUser_AddConstraints()
    {
        $table = $this->getTableStatement('User', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'Person_ID', 'Person', 'Person_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'UserState_Code', 'UserState', 'UserState_Code'));
        $this->addConstraintToTable($table, new UniqueKey('User_Username'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Locale_Code', 'Locale', 'Locale_Code'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }


    public function updateTableUserRole()
    {
        $table = $this->getTableStatement('UserRole');
        $this->addColumnToTable($table, new Integer('UserRole_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Varchar('UserRole_Code', 255));
        $this->addColumnToTable($table, new Varchar('UserRole_Name', 255));
        $this->addColumnToTable($table, new Boolean('UserRole_Active'))
            ->setDefault(true);
        $this->addConstraintToTable($table, new PrimaryKey('UserRole_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableUserRole_DropContraints()
    {
        $table = $this->getTableStatement('UserRole', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('UserRole_ID'));
        $this->dropConstraintFromTable($table, new Index('UserRole_Code'));
        $this->dropConstraintFromTable($table, new UniqueKey('UserRole_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableUserRole_AddContraints()
    {
        $table = $this->getTableStatement('UserRole', true);
        $this->addConstraintToTable($table, new Index('UserRole_Code'));
        $this->addConstraintToTable($table, new UniqueKey('UserRole_Code'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableUser_UserRole()
    {
        $table = $this->getTableStatement('User_UserRole');
        $this->addColumnToTable($table, new Integer('Person_ID'));
        $this->addColumnToTable($table, new Integer('UserRole_ID'));
        $this->addConstraintToTable($table, new PrimaryKey(['Person_ID', 'UserRole_ID']));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableUser_UserRole_DropConstraints()
    {
        $table = $this->getTableStatement('User_UserRole', true);
        $this->dropConstraintFromTable($table, new PrimaryKey(['Person_ID', 'UserRole_ID']));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Person_ID', 'User', 'Person_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'UserRole_ID', 'UserRole', 'UserRole_ID', 'CASCADE'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableUser_UserRole_AddConstraints()
    {
        $table = $this->getTableStatement('User_UserRole', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'Person_ID', 'User', 'Person_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'UserRole_ID', 'UserRole', 'UserRole_ID', 'CASCADE'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableUserPermission()
    {
        $table = $this->getTableStatement('UserPermission');
        $this->addColumnToTable($table, new Varchar('UserPermission_Code', 255));
        $this->addColumnToTable($table, new Boolean('UserPermission_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('UserPermission_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableUserPermission_DropConstraints()
    {
        $table = $this->getTableStatement('UserPermission', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('UserPermission_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableUserPermission_AddConstraints()
    {
        $table = $this->getTableStatement('UserPermission', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableUserRole_UserPermission()
    {
        $table = $this->getTableStatement('UserRole_UserPermission');
        $this->addColumnToTable($table, new Integer('UserRole_ID'));
        $this->addColumnToTable($table, new Varchar('UserPermission_Code', 255));
        $this->addConstraintToTable($table, new PrimaryKey(['UserRole_ID', 'UserPermission_Code']));
        $this->addDefaultColumnsToTable($table);

        return $this->query($table);
    }

    public function updateTableUserRole_UserPermission_DropConstraints()
    {
        $table = $this->getTableStatement('UserRole_UserPermission', true);
        $this->dropConstraintFromTable($table, new PrimaryKey(['UserRole_ID', 'UserPermission_Code']));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'UserRole_ID', 'UserRole', 'UserRole_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'UserPermission_Code', 'UserPermission', 'UserPermission_Code', 'CASCADE'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableUserRole_UserPermission_AddConstraints()
    {
        $table = $this->getTableStatement('UserRole_UserPermission', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'UserRole_ID', 'UserRole', 'UserRole_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'UserPermission_Code', 'UserPermission', 'UserPermission_Code', 'CASCADE'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableTranslation()
    {
        $table = $this->getTableStatement('Translation');
        $this->addColumnToTable($table, new Integer('Translation_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Varchar('Translation_Code', 255));
        $this->addColumnToTable($table, new Varchar('Locale_Code', 255));
        $this->addColumnToTable($table, new Varchar('Translation_Namespace', 255));
        $this->addColumnToTable($table, new Text('Translation_Text', 65535, true));
        $this->addConstraintToTable($table, new PrimaryKey('Translation_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableTranslation_DropConstraints()
    {
        $table = $this->getTableStatement('Translation', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('Translation_ID'));
        $this->dropConstraintFromTable($table, new UniqueKey(['Translation_Code', 'Locale_Code', 'Translation_Namespace']));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Locale_Code', 'Locale', 'Locale_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableTranslation_AddConstraints()
    {
        $table = $this->getTableStatement('Translation', true);
        $this->addConstraintToTable($table, new UniqueKey(['Translation_Code', 'Locale_Code', 'Translation_Namespace']));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Locale_Code', 'Locale', 'Locale_Code'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableArticle()
    {
        $table = $this->getTableStatement('Article');
        $this->addColumnToTable($table, new Integer('Article_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Varchar('Article_Code', 255, true));
        $this->addColumnToTable($table, new Text('Article_Data', 65535, true));
        $this->addConstraintToTable($table, new PrimaryKey('Article_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableArticle_DropConstraints()
    {
        $table = $this->getTableStatement('Article', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('Article_ID'));
        $this->dropConstraintFromTable($table, new UniqueKey('Article_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableArticle_AddConstraints()
    {
        $table = $this->getTableStatement('Article', true);
        $this->addConstraintToTable($table, new UniqueKey('Article_Code'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableArticleData()
    {
        $table = $this->getTableStatement('ArticleData');
        $this->addColumnToTable($table, new Integer('ArticleData_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Integer('Article_ID'));
        $this->addColumnToTable($table, new Text('ArticleData_Data', 65535, true));
        $this->addColumnToTable($table, new Boolean('ArticleData_Active', true, 1));
        $this->addColumnToTable($table, new Timestamp('ArticleData_Timestamp', true));
        $this->addConstraintToTable($table, new PrimaryKey('ArticleData_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableArticleData_DropConstraints()
    {
        $table = $this->getTableStatement('ArticleData', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('ArticleData_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID', 'CASCADE'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableArticleData_AddConstraints()
    {
        $table = $this->getTableStatement('ArticleData', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID', 'CASCADE'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableArticleTranslation()
    {
        $table = $this->getTableStatement('ArticleTranslation');
        $this->addColumnToTable($table, new Integer('Article_ID'));
        $this->addColumnToTable($table, new Varchar('Locale_Code', 255));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_Code', 255));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_Host', 255, true));
        $this->addColumnToTable($table, new Boolean('ArticleTranslation_Active', false, 1));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_Name', 255));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_Title', 255, true));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_Keywords', 255, true));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_Heading', 255, true));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_SubHeading', 255, true));
        $this->addColumnToTable($table, new Varchar('ArticleTranslation_Path', 255, true));
        $this->addColumnToTable($table, new Text('ArticleTranslation_Teaser', 65535, true));
        $this->addColumnToTable($table, new Text('ArticleTranslation_Text', 65535, true));
        $this->addColumnToTable($table, new Text('ArticleTranslation_Footer', 65535, true));
        $this->addColumnToTable($table, new Integer('File_ID', true));
        $this->addConstraintToTable($table, new PrimaryKey(['Article_ID', 'Locale_Code']));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableArticleTranslation_DropConstraints()
    {
        $table = $this->getTableStatement('ArticleTranslation', true);
        $this->dropConstraintFromTable($table, new PrimaryKey(['Article_ID', 'Locale_Code']));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Locale_Code', 'Locale', 'Locale_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'File_ID', 'File', 'File_ID'));
        $this->dropConstraintFromTable($table, new UniqueKey(['Locale_Code', 'ArticleTranslation_Code']));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableArticleTranslation_AddConstraints()
    {
        $table = $this->getTableStatement('ArticleTranslation', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Locale_Code', 'Locale', 'Locale_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'File_ID', 'File', 'File_ID'));
        $this->addConstraintToTable($table, new UniqueKey(['Locale_Code', 'ArticleTranslation_Code']));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenuState()
    {
        $table = $this->getTableStatement('CmsMenuState');
        $this->addColumnToTable($table, new Varchar('CmsMenuState_Code', 255));
        $this->addColumnToTable($table, new Boolean('CmsMenuState_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsMenuState_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenuState_DropConstraints()
    {
        $table = $this->getTableStatement('CmsMenuState', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsMenuState_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenuState_AddConstraints()
    {
        $table = $this->getTableStatement('CmsMenuState', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenuType()
    {
        $table = $this->getTableStatement('CmsMenuType');
        $this->addColumnToTable($table, new Varchar('CmsMenuType_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsMenuType_Template', 255));
        $this->addColumnToTable($table, new Boolean('CmsMenuType_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsMenuType_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenuType_DropConstraints()
    {
        $table = $this->getTableStatement('CmsMenuType', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsMenuType_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenuType_AddConstraints()
    {
        $table = $this->getTableStatement('CmsMenuType', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageState()
    {
        $table = $this->getTableStatement('CmsPageState');
        $this->addColumnToTable($table, new Varchar('CmsPageState_Code', 255));
        $this->addColumnToTable($table, new Boolean('CmsPageState_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsPageState_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageState_DropConstraints()
    {
        $table = $this->getTableStatement('CmsPageState', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsPageState_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageState_AddConstraints()
    {
        $table = $this->getTableStatement('CmsPageState', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageType()
    {
        $table = $this->getTableStatement('CmsPageType');
        $this->addColumnToTable($table, new Varchar('CmsPageType_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsPageType_Template', 255));
        $this->addColumnToTable($table, new Boolean('CmsPageType_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsPageType_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageType_DropConstraints()
    {
        $table = $this->getTableStatement('CmsPageType', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsPageType_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageType_AddConstraints()
    {
        $table = $this->getTableStatement('CmsPageType', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageLayout()
    {
        $table = $this->getTableStatement('CmsPageLayout');
        $this->addColumnToTable($table, new Varchar('CmsPageLayout_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsPageLayout_Template', 255));
        $this->addColumnToTable($table, new Boolean('CmsPageLayout_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsPageLayout_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageLayout_DropConstraints()
    {
        $table = $this->getTableStatement('CmsPageLayout', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsPageLayout_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPageLayout_AddConstraints()
    {
        $table = $this->getTableStatement('CmsPageLayout', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsBlockState()
    {
        $table = $this->getTableStatement('CmsBlockState');
        $this->addColumnToTable($table, new Varchar('CmsBlockState_Code', 255));
        $this->addColumnToTable($table, new Boolean('CmsBlockState_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsBlockState_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsBlockState_DropConstraints()
    {
        $table = $this->getTableStatement('CmsBlockState', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsBlockState_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsBlockState_AddConstraints()
    {
        $table = $this->getTableStatement('CmsBlockState', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }


    public function updateTableCmsBlockType()
    {
        $table = $this->getTableStatement('CmsBlockType');
        $this->addColumnToTable($table, new Varchar('CmsBlockType_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsBlockType_Template', 255));
        $this->addColumnToTable($table, new Boolean('CmsBlockType_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsBlockType_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsBlockType_DropConstraints()
    {
        $table = $this->getTableStatement('CmsBlockType', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsBlockType_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsBlockType_AddConstraints()
    {
        $table = $this->getTableStatement('CmsBlockType', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPostState()
    {
        $table = $this->getTableStatement('CmsPostState');
        $this->addColumnToTable($table, new Varchar('CmsPostState_Code', 255));
        $this->addColumnToTable($table, new Boolean('CmsPostState_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsPostState_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPostState_DropConstraints()
    {
        $table = $this->getTableStatement('CmsPostState', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsPostState_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPostState_AddConstraints()
    {
        $table = $this->getTableStatement('CmsPostState', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPostType()
    {
        $table = $this->getTableStatement('CmsPostType');
        $this->addColumnToTable($table, new Varchar('CmsPostType_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsPostType_Template', 255));
        $this->addColumnToTable($table, new Boolean('CmsPostType_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('CmsPostType_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPostType_DropConstraints()
    {
        $table = $this->getTableStatement('CmsPostType', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsPostType_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPostType_AddConstraints()
    {
        $table = $this->getTableStatement('CmsPostType', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPage()
    {
        $table = $this->getTableStatement('CmsPage');
        $this->addColumnToTable($table, new Integer('CmsPage_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Integer('Article_ID'));
        $this->addColumnToTable($table, new Varchar('CmsPageState_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsPageType_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsPageLayout_Code', 255, false, 'default'));
        $this->addColumnToTable($table, new Integer('CmsPage_ID_Redirect', true));
        $this->addConstraintToTable($table, new PrimaryKey('CmsPage_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPage_DropConstraints()
    {
        $table = $this->getTableStatement('CmsPage', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsPage_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPageState_Code', 'CmsPageState', 'CmsPageState_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPageType_Code', 'CmsPageType', 'CmsPageType_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPageLayout_Code', 'CmsPageLayout', 'CmsPageLayout_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPage_ID_Redirect', 'CmsPage', 'CmsPage_ID'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPage_AddConstraints()
    {
        $table = $this->getTableStatement('CmsPage', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPageState_Code', 'CmsPageState', 'CmsPageState_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPageType_Code', 'CmsPageType', 'CmsPageType_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPageLayout_Code', 'CmsPageLayout', 'CmsPageLayout_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPage_ID_Redirect', 'CmsPage', 'CmsPage_ID'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }


    public function updateTableCmsBlock()
    {
        $table = $this->getTableStatement('CmsBlock');
        $this->addColumnToTable($table, new Integer('CmsBlock_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Integer('Article_ID'));
        $this->addColumnToTable($table, new Varchar('CmsBlockState_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsBlockType_Code', 255));
        $this->addConstraintToTable($table, new PrimaryKey('CmsBlock_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }


    public function updateTableCmsBlock_DropConstraints()
    {
        $table = $this->getTableStatement('CmsBlock', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsBlock_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsBlockState_Code', 'CmsBlockState', 'CmsBlockState_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsBlockType_Code', 'CmsBlockType', 'CmsBlockType_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsBlock_AddConstraints()
    {
        $table = $this->getTableStatement('CmsBlock', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsBlockState_Code', 'CmsBlockState', 'CmsBlockState_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsBlockType_Code', 'CmsBlockType', 'CmsBlockType_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPage_CmsBlock()
    {
        $table = $this->getTableStatement('CmsPage_CmsBlock');
        $this->addColumnToTable($table, new Integer('CmsPage_ID'));
        $this->addColumnToTable($table, new Integer('CmsBlock_ID'));
        $this->addColumnToTable($table, new Integer('CmsPage_CmsBlock_Order'));
        $this->addConstraintToTable($table, new PrimaryKey(['CmsPage_ID', 'CmsBlock_ID']));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPage_CmsBlock_DropConstraints()
    {
        $table = $this->getTableStatement('CmsPage_CmsBlock', true);
        $this->dropConstraintFromTable($table, new PrimaryKey(['CmsPage_ID', 'CmsBlock_ID']));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPage_ID', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsBlock_ID', 'CmsBlock', 'CmsBlock_ID', 'CASCADE'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPage_CmsBlock_AddConstraints()
    {
        $table = $this->getTableStatement('CmsPage_CmsBlock', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPage_ID', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsBlock_ID', 'CmsBlock', 'CmsBlock_ID', 'CASCADE'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPost()
    {
        $table = $this->getTableStatement('CmsPost');
        $this->addColumnToTable($table, new Integer('CmsPost_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Integer('CmsPage_ID'));
        $this->addColumnToTable($table, new Integer('Article_ID'));
        $this->addColumnToTable($table, new Timestamp('CmsPost_PublishTimestamp'));
        $this->addColumnToTable($table, new Varchar('CmsPostState_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsPostType_Code', 255));
        $this->addConstraintToTable($table, new PrimaryKey('CmsPost_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPost_DropConstraint()
    {
        $table = $this->getTableStatement('CmsPost', true);
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPostState_Code', 'CmsPostState', 'CmsPostState_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPostType_Code', 'CmsPostType', 'CmsPostType_Code'));
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsPost_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPage_ID', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsPost_AddConstraint()
    {
        $table = $this->getTableStatement('CmsPost', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPostState_Code', 'CmsPostState', 'CmsPostState_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPostType_Code', 'CmsPostType', 'CmsPostType_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPage_ID', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }


    public function updateTableCmsMenu()
    {
        $table = $this->getTableStatement('CmsMenu');
        $this->addColumnToTable($table, new Integer('CmsMenu_ID'))
            ->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Integer('CmsMenu_ID_Parent', true));
        $this->addColumnToTable($table, new Integer('CmsPage_ID'));
        $this->addColumnToTable($table, new Integer('CmsPage_ID_Parent', true));
        $this->addColumnToTable($table, new Integer('CmsMenu_Order', false, 0));
        $this->addColumnToTable($table, new Varchar('CmsMenuState_Code', 255));
        $this->addColumnToTable($table, new Varchar('CmsMenuType_Code', 255, true));
        $this->addConstraintToTable($table, new PrimaryKey('CmsMenu_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenu_DropConstraints()
    {
        $table = $this->getTableStatement('CmsMenu', true);
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsMenuState_Code', 'CmsMenuState', 'CmsMenuState_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsMenuType_Code', 'CmsMenuType', 'CmsMenuType_Code'));
        $this->dropConstraintFromTable($table, new PrimaryKey('CmsMenu_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPage_ID', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsPage_ID_Parent', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'CmsMenu_ID_Parent', 'CmsMenu', 'CmsMenu_ID', 'CASCADE'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableCmsMenu_AddConstraints()
    {
        $table = $this->getTableStatement('CmsMenu', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsMenuState_Code', 'CmsMenuState', 'CmsMenuState_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsMenuType_Code', 'CmsMenuType', 'CmsMenuType_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPage_ID', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsPage_ID_Parent', 'CmsPage', 'CmsPage_ID', 'CASCADE'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'CmsMenu_ID_Parent', 'CmsMenu', 'CmsMenu_ID', 'CASCADE'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableImportType()
    {
        $table = $this->getTableStatement('ImportType');
        $this->addColumnToTable($table, new Varchar('ImportType_Code', 255));
        $this->addColumnToTable($table, new Boolean('ImportType_Active'));
        $this->addConstraintToTable($table, new PrimaryKey('ImportType_Code'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableImportType_DropConstraints()
    {
        $table = $this->getTableStatement('ImportType', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('ImportType_Code'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableImportType_AddConstraints()
    {
        $table = $this->getTableStatement('ImportType', true);
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }

    public function updateTableImport()
    {
        $table = $this->getTableStatement('Import');
        $this->addColumnToTable($table, new Integer('Import_ID'))->setOption('AUTO_INCREMENT', true);
        $this->addColumnToTable($table, new Integer('Article_ID', false));
        $this->addColumnToTable($table, new Varchar('ImportType_Code', 255, false));
        $this->addColumnToTable($table, new Varchar('Import_Name', 255, false));
        $this->addColumnToTable($table, new Text('Import_Data', 65535, true));
        $this->addColumnToTable($table, new Boolean('Import_Active', false, 0));
        $this->addColumnToTable($table, new Integer('Import_Day', true));
        $this->addColumnToTable($table, new Integer('Import_Hour', true));
        $this->addColumnToTable($table, new Integer('Import_Minute', true));
        $this->addConstraintToTable($table, new PrimaryKey('Import_ID'));
        $this->addDefaultColumnsToTable($table);
        return $this->query($table);
    }

    public function updateTableImport_DropConstraints()
    {
        $table = $this->getTableStatement('Import', true);
        $this->dropConstraintFromTable($table, new PrimaryKey('Import_ID'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'ImportType_Code', 'ImportType', 'ImportType_Code'));
        $this->dropConstraintFromTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID'));
        $this->dropDefaultConstraintsFromTable($table);
        return $this->query($table);
    }

    public function updateTableImport_AddConstraints()
    {
        $table = $this->getTableStatement('Import', true);
        $this->addConstraintToTable($table, new ForeignKey(null, 'ImportType_Code', 'ImportType', 'ImportType_Code'));
        $this->addConstraintToTable($table, new ForeignKey(null, 'Article_ID', 'Article', 'Article_ID'));
        $this->addDefaultConstraintsToTable($table);
        return $this->query($table);
    }
}
