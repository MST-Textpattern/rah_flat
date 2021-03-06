<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2013 Jukka Svahn
 *
 * This file is part of rah_flat.
 *
 * rah_flat is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * rah_flat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rah_flat. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Base class for import definitions.
 *
 * @example
 * class MyImportDefinition extends Rah_Flat_Import_Base
 * {
 *     public function getPanelName()
 *     {
 *         return 'MyPanel';
 *     }
 * }
 */

abstract class Rah_Flat_Import_Base implements Rah_Flat_Import_Template
{
    /**
     * The directory.
     *
     * @var string
     */

    protected $directory;

    /**
     * An array of database table columns.
     *
     * @var array
     */

    private $columns = array();

    /**
     * {@inheritdoc}
     */

    public function __construct($directory)
    {
        $this->directory = $directory;
        register_callback(array($this, 'init'), 'rah_flat.import');
    }

    /**
     * {@inheritdoc}
     */

    public function getTemplateIterator($directory)
    {
        return new Rah_Flat_TemplateIterator($directory);
    }

    /**
     * {@inheritdoc}
     */

    public function init()
    {
        if ($directory = get_pref('rah_flat_path', '', true))
        {
            $directory = txpath . '/' . $directory . '/' . $this->directory;

            if (file_exists($directory) && is_dir($directory) && is_readable($directory))
            {
                $template = $this->getTemplateIterator($directory);

                while ($template->valid())
                {
                    if ($this->importTemplate($template) === false)
                    {
                        throw new Exception('Unable to import ' . $template->getTemplateName());
                    }

                    $template->next();
                }

                $this->dropRemoved($template);
            }
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
        unset($GLOBALS['txp_permissions'][$this->getPanelName()]);
    }

    /**
     * {@inheritdoc}
     */

    public function getTableColumns()
    {
        if (!$this->columns)
        {
            $this->columns = doArray((array) @getThings('describe '.safe_pfx($this->getTableName())), 'strtolower');
        }

        return $this->columns;
    }
}