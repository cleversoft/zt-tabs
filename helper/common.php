<?php

/**
 * ZT Tabs
 * 
 * @package     Joomla
 * @subpackage  Module
 * @version     1.0.0
 * @author      ZooTemplate 
 * @email       support@zootemplate.com 
 * @link        http://www.zootemplate.com 
 * @copyright   Copyright (c) 2015 ZooTemplate
 * @license     GPL v2
 */
defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();
$db = JFactory::getDBO();
require_once (JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php');

class ZtTabsHelperCommon
{

    /**
     *
     * @var array 
     */
    private $_config;

    /**
     *
     * @var array
     */
    private $_parseData;

    /**
     * 
     * @param JRegistry $params
     */
    public function __construct($params)
    {

        $default = array(
            'arrayTabs' => array(),
            'maxNumberCategory' => 5,
            'showIntroImage' => 1,
            'itemsOrdering' => 'default',
            'introTextLength' => '200',
            'tab_style' => '',
            'tab_alignment' => '',
            'title_position' => '',
            'effect_type' => '',
            'tWidth' => '',
            'tHeight' => '',
            'tab_maxItem' => '',
            'intro_image_width' => '100',
            'intro_image_height' => '100'
        );
        /**
         * Merge with params
         */
        $this->_config = array_merge($default, $params->toArray());
        $this->parsedData = array(
            'arrayTabs' => array(),
            'showReadMore' => 1,
            'tab_style' => '',
            'tab_alignment' => '',
            'title_position' => '',
            'effect_type' => '',
            'tWidth' => '',
            'tHeight' => '',
            'intro_image_width' => '100',
            'intro_image_height' => '100',
            'tab_maxItem' => '',
            'showIntroImage' => 1
        );
    }

    /**
     * 
     */
    public function createdDirThumb()
    {
        $thumbImgParentFolder = JPATH_BASE . DS . 'cache' . DS . 'zt-assets';
        if (!JFolder::exists($thumbImgParentFolder))
        {
            JFolder::create($thumbImgParentFolder);
        }
    }

    //check published module and category in arrTabs
    public function checkData($tab)
    {
        $tableName = '';
        if ($tab[0] == 'module')
        {
            $tableName = '#__modules';
        } elseif ($tab[0] == 'category')
        {
            $tableName = '#__categories';
        }
        // Get a db connection.
        $db = JFactory::getDbo();

        // Create a new query object.
        $query = $db->getQuery(true);
        $query
                ->select($db->quoteName('published'))
                ->from($db->quoteName($tableName, 'c'))
                ->where($db->quoteName('c.id') . " = " . $db->quote($tab[1]));

        $db->setQuery($query);
        return $db->loadResult();
    }

    public function parseData()
    {
        $tab_maxItem = $this->config['tab_maxItem'];
        $arrTab = $this->config['arrayTabs'];
        $count = count($arrTab);
        if ($count > $tab_maxItem)
        {
            $count = $tab_maxItem;
        }
        for ($i = 0; $i < $count; $i++)
        {
            $tab = explode('_', $arrTab[$i]);
            if ($this->checkData($tab))
            {
                $this->parsedData['arrayTabs'][$i] = $tab;
            }
        }

        // $this->parsedData['arrayTabs'] hien ca module va category ra, 0,1,2 laf module; 3 la category
        $this->parsedData['tab_style'] = $this->config['tab_style'];
        $this->parsedData['title_position'] = $this->config['title_position'];

        $this->parsedData['tab_alignment'] = $this->config['tab_alignment'];
        $this->parsedData['effect_type'] = $this->config['effect_type'];
        $this->parsedData['showIntroImage'] = $this->config['showIntroImage'];

        $this->parsedData['tWidth'] = ($this->config['tWidth'] == 'auto') ? $this->config['tWidth'] : $this->config['tWidth'] . 'px';
        $this->parsedData['tHeight'] = ($this->config['tHeight'] == 'auto') ? $this->config['tHeight'] : $this->config['tHeight'] . 'px';
        $this->parsedData['intro_image_width'] = $this->config['intro_image_width'] . 'px';
        $this->parsedData['intro_image_height'] = $this->config['intro_image_height'] . 'px';

        $this->parsedData['tab_maxItem'] = $this->config['tab_maxItem'];
    }

    //Get title module by id input
    public function getCategoryTileById($catId)
    {
        $db = JFactory::getDBO();
        $sql = "SELECT title
				FROM #__categories AS c
				WHERE c.published = 1  AND c.id ='" . $catId . "'";
        $db->setQuery($sql);
        return $db->loadResult();
    }

    //End get title Category
    //Get title module by id input
    public function getModuleTitleById($moduleId)
    {
        $db = JFactory::getDBO();
        $sql = "SELECT title FROM #__modules WHERE id=" . $moduleId;
        $db->setQuery($sql);
        return $db->loadResult();
    }

    //End get title Module
    //Get content Module
    public function parseTabModuleById($moduleId)
    {
        //echo ' chay den day';
        $modules = & $this->_load();
        $result = '';
        //var_dump($modules);
        foreach ($modules as $module)
        {
            //var_dump($module->id== $moduleId);
            if (((int) $module->id == (int) $moduleId) && $module)
                $result = $module;
        }

        return $result;
    }

    //End get Content
    //Get content Category
    public function getListContentArticle($catId)
    {
        $db = JFactory::getDBO();
        $nullDate = $db->getNullDate();
        $date = JFactory::getDate();
        $now = $date->toSql();
        $count = $this->config['maxNumberCategory'];


        // Ordering
        switch ($this->config['itemsOrdering'])
        {
            case 'm_dsc' :
                $ordering = array('a.modified DESC', ' a.created DESC');
                break;
            case 'c_dsc' :
                $ordering = ' a.ordering ';
                break;
            default :
                $ordering = ' a.ordering';
                break;
        }
        $query = $db->getQuery(true);

        $query
                ->select(' a.* ')
                ->select($db->quoteName('c.id', 'idCategory'))
                ->select($db->quoteName('c.alias', 'alCategory'))
                ->select($db->quoteName('c.published'))
                ->select($db->quoteName('b.username', 'user'))
                ->from($db->quoteName('#__content', 'a'))
                ->join('INNER', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')')
                ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')')
                ->where($db->quoteName('a.catid') . ' = ' . $catId)
                ->where($db->quoteName('a.state') . ' = 1')
                ->where($db->quoteName('c.published') . ' = 1')
                ->where($db->quoteName('a.publish_up') . ' BETWEEN  ' . $db->Quote($nullDate) . ' AND ' . $db->Quote($now))
                ->where($db->quoteName('a.publish_down') . ' BETWEEN  ' . $db->Quote($nullDate) . ' AND ' . $db->Quote($now))
                ->order($ordering)
        ;

        $db->setQuery($query, 0, $count);

        $rows = $db->loadObjectList();


        $lists = array();

        $j = 0;
        if (count($rows))
        {
            foreach ($rows as $row)
            {
                //var_dump($row);
                $lists[$j] = new stdClass();
                $lists[$j]->id = $row->id;
                $lists[$j]->title = $row->title;
                $lists[$j]->link = JRoute::_(ContentHelperRoute::getArticleRoute($row->id . ':' . $row->alias, $row->idCategory . ':' . $row->alCategory, $row->id));

                $lists[$j]->user = $row->user;
                $date = strtotime($row->created);
                $lists[$j]->created = date('F j, Y', $date);

                $lists[$j]->introText = $this->introText($row->introtext);
                $img = json_decode($row->images, true);
                $lists[$j]->introImage = $img['image_intro'];
                $j++;
            }
        }
        return $lists;
    }

    //End get content Category

    public function &_load()
    {
        global $app, $itemId;
        static $modules;

        if (isset($modules))
        {
            return $modules;
        }

        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        $aid = (!$user->get('aid', 1)) ? 1 : $user->get('aid', 1);

        $modules = array();

        $wheremenu = isset($itemId) ? ' AND ( mm.menuid = ' . (int) $itemId . ' OR mm.menuid = 0 )' : '';
        $query = 'SELECT id, IF(title="","","") as title , module, position, content, showtitle, params'
                . ' FROM #__modules AS m'
                . ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id'
                . ' WHERE m.published = 1'
                . ' AND m.access <= ' . (int) $aid
                . ' AND m.client_id = ' . (int) $app->getClientId()
                . $wheremenu
                . ' ORDER BY position, ordering';
        // var_dump($aid);
        $db->setQuery($query);

        if (null === ($modules = $db->loadObjectList()))
        {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('Error Loading Modules') . $db->getErrorMsg());
            return false;
        }

        $total = count($modules);
        for ($i = 0; $i < $total; $i++)
        {
            //determine if this is a custom module
            $file = $modules[$i]->module;
            $custom = substr($file, 0, 4) == 'mod_' ? 0 : 1;
            $modules[$i]->user = $custom;
            // CHECK: custom module name is given by the title field, otherwise it's just 'om' ??
            $modules[$i]->name = $custom ? $modules[$i]->title : substr($file, 4);
            $modules[$i]->style = null;
            $modules[$i]->position = strtolower($modules[$i]->position);
        }

        return $modules;
    }

    public function introText($content)
    {
        $introLength = intval($this->config['introTextLength']);
        $content = preg_replace("/<img[^>]+\>/i", "", $content);
        $content = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $content);
        // $content = preg_replace("<p></p>", "", $content);
        $length = strlen($content);
        if ($length > $introLength)
        {
            $content = substr($content, 0, $introLength);
            $content .= '...';
        }
        return $content;
    }

    /**
     * Get modules with grouped by positions
     * @return type
     */
    public static function getModules()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
                ->from('#__modules')
                ->where($db->quoteName('published') . '=' . 1)
                ->where($db->quoteName('client_id') . '=' . 0);
        $db->setQuery($query);
        $modules = $db->loadObjectList();
        foreach ($modules as $module)
        {
            $list[$module->position][] = $module;
        }
        return $list;
    }

    public static function getModule($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
                ->from('#__modules')
                ->where($db->quoteName('published') . '=' . 1)
                ->where($db->quoteName('id') . '=' . (int) $db);
        $db->setQuery($query);
        $module = $db->loadObject();
        if ($module)
        {
            $module->params = new JRegistry($module->params);
        }
        return $module;
    }

    public static function getData($data)
    {
        if (is_string($data))
        {
            $data = json_decode($data);
        }
        $list = array();
        foreach ($data as $item)
        {
            switch ($item->type)
            {
                case 'module':
                    $module['data'] = self::getModule($item->value);
                    $attribs['style'] = 'xhtml';
                    $module['html'] = JModuleHelper::renderModule($module['data'], $attribs);
                    $list[] = $module;
                    break;
                case 'categories':
                    $extension = $item->extension;
                    $className = 'ZtTabsHelper' . ucfirst($extension);
                    if (class_exists($className))
                    {
                        $list[] = call_user_func_array(array($className, 'getData'), array($item));
                    }
                    break;
            }
        }
        return $list;
    }

}
