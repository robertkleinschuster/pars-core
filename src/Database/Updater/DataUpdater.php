<?php

namespace Pars\Core\Database\Updater;


class DataUpdater extends AbstractUpdater
{

    public function getCode(): string
    {
        return 'data';
    }


    public function updateDataConfig()
    {
        $i = 1;
        $data_Map = [];
        $data_Map[] = [
            'Config_Code' => 'asset.domain',
            'Config_Value' => ''
        ];
        $data_Map[] = [
            'Config_Code' => 'locale.default',
            'Config_Value' => 'de_AT'
        ];
        $data_Map[] = [
            'Config_Code' => 'admin.timezone',
            'Config_Value' => 'UTC'
        ];
        $data_Map[] = [
            'Config_Code' => 'admin.title',
            'Config_Value' => 'PARS Admin'
        ];
        $data_Map[] = [
            'Config_Code' => 'admin.author',
            'Config_Value' => 'PARS'
        ];
        $data_Map[] = [
            'Config_Code' => 'admin.favicon',
            'Config_Value' => '/favicon.ico'
        ];
        $data_Map[] = [
            'Config_Code' => 'admin.description',
            'Config_Value' => 'PARS Admin'
        ];
        $data_Map[] = [
            'Config_Code' => 'admin.charset',
            'Config_Value' => 'utf-8'
        ];
        $data_Map[] = [
            'Config_Code' => 'frontend.brand',
            'Config_Value' => 'PARS'
        ];
        $data_Map[] = [
            'Config_Code' => 'frontend.domain',
            'Config_Value' => ''
        ];
        $data_Map[] = [
            'Config_Code' => 'frontend.favicon',
            'Config_Value' => ''
        ];
        $data_Map[] = [
            'Config_Code' => 'frontend.charset',
            'Config_Value' => 'utf-8'
        ];
        $data_Map[] = [
            'Config_Code' => 'frontend.author',
            'Config_Value' => ''
        ];
        $data_Map[] = [
            'Config_Code' => 'frontend.keywords',
            'Config_Value' => ''
        ];
        return $this->saveDataMap('Config', 'Config_Code', $data_Map, true);
    }


    public function updateDataUserState()
    {
        $data_Map = [];
        $data_Map[] = [
            'UserState_Code' => 'active',
            'UserState_Active' => true,
        ];
        $data_Map[] = [
            'UserState_Code' => 'inactive',
            'UserState_Active' => true,
        ];
        $data_Map[] = [
            'UserState_Code' => 'locked',
            'UserState_Active' => true,
        ];
        return $this->saveDataMap('UserState', 'UserState_Code', $data_Map);
    }

    public function updateDataLocale()
    {
        $i = 1;
        $data_Map = [];
        $data_Map[] = [
            'Locale_Code' => 'de_AT',
            'Locale_UrlCode' => 'de-AT',
            'Locale_Name' => 'Deutsch (Österreich)',
            'Locale_Active' => 1,
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'de_DE',
            'Locale_UrlCode' => 'de-DE',
            'Locale_Name' => 'Deutsch (Deutschland)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'de_BE',
            'Locale_UrlCode' => 'de-BE',
            'Locale_Name' => 'Deutsch (Belgien)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'de_LI',
            'Locale_UrlCode' => 'de-LI',
            'Locale_Name' => 'Deutsch (Liechtenstein)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'de_LU',
            'Locale_UrlCode' => 'de-LU',
            'Locale_Name' => 'Deutsch (Luxembourg)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'de_CH',
            'Locale_UrlCode' => 'de-CH',
            'Locale_Name' => 'Deutsch (Schweiz)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'en_AU',
            'Locale_UrlCode' => 'en-AU',
            'Locale_Name' => 'English (Australia)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'en_BE',
            'Locale_UrlCode' => 'en-BE',
            'Locale_Name' => 'English (Belgium)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'en_US',
            'Locale_UrlCode' => 'en-US',
            'Locale_Name' => 'English (United States)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'en_GB',
            'Locale_UrlCode' => 'en-GB',
            'Locale_Name' => 'English (United Kingdom)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'nl_NL',
            'Locale_UrlCode' => 'nl-NL',
            'Locale_Name' => 'Dutch (Netherlands)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'sl_SI',
            'Locale_UrlCode' => 'sl-SI',
            'Locale_Name' => 'Slovenian (Slovenia)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'hu_HU',
            'Locale_UrlCode' => 'hu-HU',
            'Locale_Name' => 'Hungarian (Hungary)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'it_IT',
            'Locale_UrlCode' => 'it-IT',
            'Locale_Name' => 'Italian (Italy)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'it_SM',
            'Locale_UrlCode' => 'it-SM',
            'Locale_Name' => 'Italian (San Marino)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'it_CH',
            'Locale_UrlCode' => 'it-CH',
            'Locale_Name' => 'Italian (Switzerland)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'es_ES',
            'Locale_UrlCode' => 'es-ES',
            'Locale_Name' => 'Spanish (Spain)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'es_US',
            'Locale_UrlCode' => 'es-US',
            'Locale_Name' => 'Spanish (United States)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'fr_FR',
            'Locale_UrlCode' => 'fr-FR',
            'Locale_Name' => 'French (France)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'fr_BE',
            'Locale_UrlCode' => 'fr-BE',
            'Locale_Name' => 'French (Belgium)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'fr_LU',
            'Locale_UrlCode' => 'fr-LU',
            'Locale_Name' => 'French (Luxembourg)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'fr_MC',
            'Locale_UrlCode' => 'fr-MC',
            'Locale_Name' => 'French (Monaco)',
            'Locale_Order' => $i++,
        ];
        $data_Map[] = [
            'Locale_Code' => 'fr_CH',
            'Locale_UrlCode' => 'fr-CH',
            'Locale_Name' => 'French (Switzerland)',
            'Locale_Order' => $i++,
        ];
        return $this->saveDataMap('Locale', 'Locale_Code', $data_Map, true);
    }

    public function updateDataCmsPostState()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsPostState_Code' => 'active',
            'CmsPostState_Active' => true,
        ];
        $data_Map[] = [
            'CmsPostState_Code' => 'inactive',
            'CmsPostState_Active' => true,
        ];
        return $this->saveDataMap('CmsPostState', 'CmsPostState_Code', $data_Map);
    }


    public function updateDataCmsPostType()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsPostType_Code' => 'default',
            'CmsPostType_Template' => 'cmspost::default',
            'CmsPostType_Active' => 1,
        ];
        return $this->saveDataMap('CmsPostType', 'CmsPostType_Code', $data_Map);
    }

    public function updateDataCmsPageState()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsPageState_Code' => 'active',
            'CmsPageState_Active' => true,
        ];
        $data_Map[] = [
            'CmsPageState_Code' => 'inactive',
            'CmsPageState_Active' => true,
        ];
        return $this->saveDataMap('CmsPageState', 'CmsPageState_Code', $data_Map);
    }


    public function updateDataCmsPageType()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsPageType_Code' => 'home',
            'CmsPageType_Template' => 'cmspage::home',
            'CmsPageType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsPageType_Code' => 'poll',
            'CmsPageType_Template' => 'cmspage::poll',
            'CmsPageType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsPageType_Code' => 'about',
            'CmsPageType_Template' => 'cmspage::about',
            'CmsPageType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsPageType_Code' => 'contact',
            'CmsPageType_Template' => 'cmspage::contact',
            'CmsPageType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsPageType_Code' => 'blog',
            'CmsPageType_Template' => 'cmspage::blog',
            'CmsPageType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsPageType_Code' => 'faq',
            'CmsPageType_Template' => 'cmspage::faq',
            'CmsPageType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsPageType_Code' => 'gallery',
            'CmsPageType_Template' => 'cmspage::gallery',
            'CmsPageType_Active' => 1,
        ];
        return $this->saveDataMap('CmsPageType', 'CmsPageType_Code', $data_Map);
    }


    public function updateDataCmsPageLayout()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsPageLayout_Code' => 'default',
            'CmsPageLayout_Template' => 'layout::default',
            'CmsPageLayout_Active' => 1,
        ];
        return $this->saveDataMap('CmsPageLayout', 'CmsPageLayout_Code', $data_Map);
    }

    public function updateDataCmsParagraphState()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsParagraphState_Code' => 'active',
            'CmsParagraphState_Active' => true,
        ];
        $data_Map[] = [
            'CmsParagraphState_Code' => 'inactive',
            'CmsParagraphState_Active' => true,
        ];
        return $this->saveDataMap('CmsParagraphState', 'CmsParagraphState_Code', $data_Map);
    }

    public function updateDataFileType()
    {
        $data_Map = [];
        $data_Map[] = [
            'FileType_Code' => 'jpg',
            'FileType_Mime' => 'image/jpeg',
            'FileType_Name' => 'JPEG',
            'FileType_Active' => true,
        ];
        $data_Map[] = [
            'FileType_Code' => 'png',
            'FileType_Mime' => 'image/png',
            'FileType_Name' => 'PNG',
            'FileType_Active' => true,
        ];
        return $this->saveDataMap('FileType', 'FileType_Code', $data_Map);
    }


    public function updateDataCmsParagraphType()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsParagraphType_Code' => 'text',
            'CmsParagraphType_Template' => 'cmsparagraph::text',
            'CmsParagraphType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsParagraphType_Code' => 'banner',
            'CmsParagraphType_Template' => 'cmsparagraph::banner',
            'CmsParagraphType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsParagraphType_Code' => 'video',
            'CmsParagraphType_Template' => 'cmsparagraph::video',
            'CmsParagraphType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsParagraphType_Code' => 'picture',
            'CmsParagraphType_Template' => 'cmsparagraph::picture',
            'CmsParagraphType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsParagraphType_Code' => 'link',
            'CmsParagraphType_Template' => 'cmsparagraph::link',
            'CmsParagraphType_Active' => 1,
        ];
        return $this->saveDataMap('CmsParagraphType', 'CmsParagraphType_Code', $data_Map);
    }


    public function updateDataCmsMenuState()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsMenuState_Code' => 'active',
            'CmsMenuState_Active' => true,
        ];
        $data_Map[] = [
            'CmsMenuState_Code' => 'inactive',
            'CmsMenuState_Active' => true,
        ];
        return $this->saveDataMap('CmsMenuState', 'CmsMenuState_Code', $data_Map);
    }


    public function updateDataCmsMenuType()
    {
        $data_Map = [];
        $data_Map[] = [
            'CmsMenuType_Code' => 'header',
            'CmsMenuType_Template' => 'cmsmenu::header',
            'CmsMenuType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsMenuType_Code' => 'footer',
            'CmsMenuType_Template' => 'cmsmenu::footer',
            'CmsMenuType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsMenuType_Code' => 'aside_left',
            'CmsMenuType_Template' => 'cmsmenu::aside_left',
            'CmsMenuType_Active' => 1,
        ];
        $data_Map[] = [
            'CmsMenuType_Code' => 'aside_right',
            'CmsMenuType_Template' => 'cmsmenu::aside_right',
            'CmsMenuType_Active' => 1,
        ];
        return $this->saveDataMap('CmsMenuType', 'CmsMenuType_Code', $data_Map);
    }


    public function updateDataUserPermission()
    {
        $data_Map = [];

        $data_Map[] = [
            'UserPermission_Code' => 'content',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'media',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'system',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'article',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'article.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'article.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'article.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'config',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'config.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'config.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'config.edit',
            'UserPermission_Active' => true,
        ];


        $data_Map[] = [
            'UserPermission_Code' => 'cmsmenu',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmsmenu.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmsmenu.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmsmenu.edit',
            'UserPermission_Active' => true,
        ];


        $data_Map[] = [
            'UserPermission_Code' => 'cmspage',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspage.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspage.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspage.edit',
            'UserPermission_Active' => true,
        ];


        $data_Map[] = [
            'UserPermission_Code' => 'cmspost',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspost.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspost.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspost.edit',
            'UserPermission_Active' => true,
        ];


        $data_Map[] = [
            'UserPermission_Code' => 'cmsparagraph',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmsparagraph.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmsparagraph.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmsparagraph.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'cmspageparagraph',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspageparagraph.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspageparagraph.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'cmspageparagraph.edit',
            'UserPermission_Active' => true,
        ];


        $data_Map[] = [
            'UserPermission_Code' => 'user',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'user.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'user.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'user.edit',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'user.edit.state',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'role',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'role.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'role.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'role.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'userrole',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'userrole.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'userrole.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'userrole.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'rolepermission',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'rolepermission.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'rolepermission.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'rolepermission.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'translation',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'translation.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'translation.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'translation.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'locale',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'locale.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'locale.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'locale.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'update',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'update.schema',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'update.data',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'update.special',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'file',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'file.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'file.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'file.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'filedirectory',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'filedirectory.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'filedirectory.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'filedirectory.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'import',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'import.delete',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'import.create',
            'UserPermission_Active' => true,
        ];
        $data_Map[] = [
            'UserPermission_Code' => 'import.edit',
            'UserPermission_Active' => true,
        ];

        $data_Map[] = [
            'UserPermission_Code' => 'debug',
            'UserPermission_Active' => true,
        ];

        return $this->saveDataMap('UserPermission', 'UserPermission_Code', $data_Map);
    }

    /**
     * @return array
     */
    public function updateDataImportType()
    {
        $data_Mao[] = [
            'ImportType_Code' => 'tesla',
            'ImportType_Active' => 1
        ];
        return $this->saveDataMap('ImportType', 'ImportType_Code', $data_Mao);
    }
}
