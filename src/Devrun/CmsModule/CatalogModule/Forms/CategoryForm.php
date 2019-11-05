<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    CategoryPresenter.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Forms;

use Devrun\CmsModule\Forms\DevrunForm;
use Nette\Application\UI\Form;

interface ICategoryFormFactory
{
    /** @return CategoryForm */
    function create();
}

class CategoryForm extends DevrunForm implements ICategoryFormFactory
{

    /** @return CategoryForm */
    function create()
    {
        parent::create();

        $this->addText('name', 'Kategorie')
            ->setAttribute('placeholder', "Název kategorie")
            ->addRule(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, NULL, 64);

        $this->addText('title', 'Titulek')
            ->setAttribute('placeholder', "Titulka")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, NULL, 128);

        $this->addText('note', 'Poznámka')
            ->setAttribute('placeholder', "Uživatelská poznámka")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, NULL, 255);

        $this->addText('description', 'Popis')
            ->setAttribute('placeholder', "Popis kategorie")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, NULL, 255);

        $this->addUpload('imageUpload', 'Obrázek')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE);

        $this->addSubmit('send', 'Uložit');


        return $this;
    }



}