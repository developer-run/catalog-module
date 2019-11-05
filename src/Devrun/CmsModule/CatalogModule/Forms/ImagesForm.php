<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ImagesForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Forms;

use Devrun\CmsModule\Forms\DevrunForm;
use Nette\Forms\Form;

interface IImagesFormFactory
{
    /** @return ImagesForm */
    function create();
}

class ImagesForm extends DevrunForm implements IImagesFormFactory
{

    /**
     * @return ImagesForm
     */
    public function create()
    {
        parent::create();

        $this->addMultiUpload('filenames', 'Obrázky')
            ->setRequired()
            ->addRule(Form::IMAGE);

        $this->addSubmit('send', 'Odeslat');



        return $this;
    }


}