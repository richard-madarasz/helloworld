<?php

namespace Solteq\TranslationManagement\Block;

use Magento\Framework\View\Element\Template\Context;
use Mageplaza\HelloWorld\Model\PostFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Registry;

/**
 * @property PostFactory _postFactory
 * @property FormKey formKey
 * @property DirectoryList dir
 */

class Index extends \Magento\Framework\View\Element\Template
{
    protected $_languageFiles = [];
    protected $_currentFile;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        FormKey $formKey,
        DirectoryList $dir,
        Registry $registry
    ) {
        $this->_postFactory = $postFactory;
        $this->formKey = $formKey;
        $this->dir = $dir;
        $this->registry = $registry;
        parent::__construct($context);
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function findLanguageFiles()
    {
        $this->listFolderFiles($this->dir->getPath('app'));
        return $this->_languageFiles;
    }

    function listFolderFiles($dir)
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (substr($file, -4) == '.csv') {
                    $this->_languageFiles[] = $dir . '/' . $file;
                }
                if (is_dir($dir . '/' . $file)) {
                    $this->listFolderFiles($dir . '/' . $file);
                }
            }
        }
        return;
    }

    function newLine($languageFile)
    {
        if (($langFile = fopen($languageFile, "a")) !== false) {
            fputcsv($langFile, ["New string", "New translation"], ",");
            fclose($langFile);
        }
        return;
    }

    function deleteLine($languageFile, $lineToDelete)
    {
        if (($langFile = fopen($languageFile, "r")) !== false) {
            while (($data = fgetcsv($langFile, 0, ",")) !== false) {
                if ($data[0] != $lineToDelete) {
                    $langArray[] = array(
                        $data[0],
                        $data[1]
                    );
                }
            }
            fclose($langFile);
        }
        $this->saveLanguageFile($langArray, $languageFile);
    }

    function saveLanguageFile($arrayToSave, $languageFile)
    {
        if (($langFile = fopen($languageFile, "w")) !== false) {
            foreach ($arrayToSave as $lines) {
                fputcsv($langFile, $lines);
            }
            fclose($langFile);
        }
        return;
    }

    function openLanguageFile($languageFile)
    {
        if (($langFile = fopen($languageFile, "r")) !== false) {
            while (($data = fgetcsv($langFile, 0, ",")) !== false) {
                $langArray[] = array(
                    $data[0],
                    $data[1]
                );
            }
            fclose($langFile);
        }
        return $langArray;
    }

    function languageFileToName($languageFile)
    {
        $nameList = array(
            'en_US.csv' => 'English',
            'fi_FI.csv' => 'Finnish',
            'hu_HU.csv' => 'Hungarian',
            'sv_SE.csv' => 'Swedish',
            'pl_PL.csv' => 'Polish',
        );
        if (strpos($languageFile, '/code')) {
            $split = explode('app/code/', $languageFile);
            $split = explode('/', $split[1]);
            return 'Module: ' . $split[0] . ' - ' . $split[1] . ' - ' . $nameList[substr($languageFile, -9)];
        } else {
            if (strpos($languageFile, '/design')) {
                $split = explode('app/design/', $languageFile);
                $split = explode('/', $split[1]);
                return 'Design: ' . ucfirst($split[0]) . ' - ' . $split[1] . ' - ' . $nameList[substr($languageFile, -9)];
            }
        }
        return;
    }

    public function getCurrentFile()
    {
        return $this->registry->registry('currentFile');
    }
}