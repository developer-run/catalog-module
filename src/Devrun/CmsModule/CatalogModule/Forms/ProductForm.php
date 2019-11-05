<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    ProductForm.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CmsModule\CatalogModule\Forms;

use Devrun\CmsModule\Forms\DevrunForm;
use Devrun\Doctrine\DoctrineForms\IComponentMapper;
use Nette\Application\UI\Form;

interface IProductFormFactory
{
    /** @return ProductForm */
    function create();
}

class ProductForm extends DevrunForm implements IProductFormFactory
{

    /**
     * @return ProductForm
     */
    public function create()
    {
        parent::create();

//        $this->addMultiSelect('categories', 'Kategorie')
//            ->setOption(IComponentMapper::ITEMS_TITLE, 'title');

        $this->addText('name', 'Název')
            ->setAttribute('placeholder', "Název produktu")
            ->addRule(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, NULL, 32);

        $this->addText('title', 'Titulek')
            ->setAttribute('placeholder', "Název titulku")
            ->addRule(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, NULL, 32);

        $this->addTextArea('description', 'Popis', 0, 8)
            ->setAttribute('placeholder', "Popis produktu")
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, NULL, 2048);

        $this->addText('price', 'Cena')
            ->setAttribute('placeholder', "Cena produktu")
            ->addRule(Form::FILLED)
            ->addRule(Form::NUMERIC);

        $this->addText('amount', 'Amount')
            ->setAttribute('placeholder', "Počet na ks")
            ->addRule(Form::FILLED)
            ->addRule(Form::NUMERIC)
            ->addRule(Form::RANGE, 'Požadovaný počet by neměl být záporný', [0, PHP_INT_MAX]);

        $this->addText('inStock', 'Na skladě')
            ->setAttribute('placeholder', "Počet ks na skladě")
            ->addRule(Form::FILLED)
            ->addRule(Form::NUMERIC)
            ->addRule(Form::RANGE, 'Požadovaný počet by neměl být záporný', [0, PHP_INT_MAX]);



//        $this->addText('dph', 'Dph')
//            ->setAttribute('placeholder', "Dph")
//            ->addRule(Form::FILLED)
//            ->addRule(Form::NUMERIC);


        $this->addSubmit('send', 'Uložit');


        return $this;
    }


}