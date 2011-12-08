<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Awards extends Module {

    public $version = '0.1';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Awards',
                'es' => 'Premios',
            ),
            'description' => array(
                'en' => 'Award entries',
                'es' => 'Entrada de Premios', 
            ),
            'frontend'  => TRUE,
            'backend'   => TRUE,
            'skip_xss'  => TRUE,
            'menu'      => 'content',

            'roles' => array(
                'put_live', 'edit_live', 'delete_live'
            )
        );
    }

    public function install()
    {
        $this->dbforge->drop_table('awards_categories');
        $this->dbforge->drop_table('awards');

        $awards_categories = "
            CREATE TABLE " . $this->db->dbprefix('awards_categories') . " (
              `id` int(11) unsigned NOT NULL auto_increment,
              `slug` varchar(20) collate utf8_unicode_ci NOT NULL default '',
              `title` varchar(20) collate utf8_unicode_ci NOT NULL default '',
              PRIMARY KEY  (`id`),
              UNIQUE KEY `slug - unique` (`slug`),
              UNIQUE KEY `title - unique` (`title`),
              KEY `slug - normal` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Awards categories.';
        ";

        $awards = "
            CREATE TABLE " . $this->db->dbprefix('awards') . " (
                `id` int(11) unsigned NOT NULL auto_increment,
                `category_id` int(11) NOT NULL,
                `author_id` int(11) NOT NULL default '0',  
                `title` varchar(100) collate utf8_unicode_ci NOT NULL default '',
                `on_frontpage` tinyint(1) NOT NULL default '0',     
                `intro` text collate utf8_unicode_ci NOT NULL,
                `slug` varchar(100) collate utf8_unicode_ci NOT NULL default '',
                `image` varchar(100) collate utf8_unicode_ci NOT NULL default 'noimage.png',
                `attachment` varchar(255) collate utf8_unicode_ci NOT NULL default '',
                `body` text collate utf8_unicode_ci NOT NULL,
                `created_on` int(11) NOT NULL,
                `updated_on` int(11) NOT NULL default 0,
                `comments_enabled` INT(1)  NOT NULL default '1',
                `status` enum('draft','live') collate utf8_unicode_ci NOT NULL default 'draft',
                PRIMARY KEY  (`id`),
                UNIQUE KEY `title` (`title`),
                KEY `category_id - normal` (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Awards';
        ";
        
        $awards_images = "
			CREATE TABLE " . $this->db->dbprefix('awards_images') . " (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `award_id` int(11) unsigned NOT NULL,
			  `filename` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
			  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		";

        if ( 
            $this->db->query( $awards_categories ) && 
            $this->db->query( $awards ) && 
            $this->db->query( $awards_images ) 
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function uninstall()
    {
        if (
            $this->dbforge->drop_table('awards')  &&
            $this->dbforge->drop_table('awards_categories') &&
            $this->dbforge->drop_table('awards_images')
        ) {        
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function upgrade($old_version)
    {
        // Your Upgrade Logic
        return TRUE;
    }

    public function help()
    {
        /**
         * Either return a string containing help info
         * return "Some help info";
         *
         * Or add a language/help_lang.php file and
         * return TRUE;
         *
         * help_lang.php contents
         * $lang['help_body'] = "Some help info";
        */
        return TRUE;
    }
}

/* End of file details.php */
