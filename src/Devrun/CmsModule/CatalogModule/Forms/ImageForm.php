<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ImageForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Forms;

use Devrun\CmsModule\Forms\DevrunForm;
use Nette\Application\UI\Form;

interface IImageFormFactory
{
    /** @return ImageForm */
    function create();
}

class ImageForm extends DevrunForm implements IImageFormFactory
{

    /**
     * @return ImageForm
     */
    public function create()
    {
        parent::create();

        $this->addTextArea('description', 'Popis')
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, null, 255);

        $this->addUpload('imageUpload', 'Obrázek')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE);

        $this->addSubmit('send', 'Odeslat');

        return $this;
    }


}