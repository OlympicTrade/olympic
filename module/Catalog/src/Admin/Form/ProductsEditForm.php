<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;

use BlogAdmin\Model\Article;
use CatalogAdmin\Model\CatalogProps;
use CatalogAdmin\Model\CatalogTypes;
use CatalogAdmin\Model\Products;
use Zend\Db\Sql\Expression;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ProductsEditForm extends Form
{
    public function setData($data)
    {
        if(!$data['attrs-tab1_title']) {
            $data['attrs-tab1_title'] = 'Как принимать {BRAND_NAME} {PRODUCT_NAME}';
        }
        if(!$data['attrs-tab1_description']) {
            $data['attrs-tab1_description'] = 'Инструкция по рекомендованному применению {PRODUCT_NAME} от бренда {BRAND_NAME}';
        }
        if(!$data['tattrs-ab1_url']) {
            $data['attrs-tab1_url'] = 'recommended';
        }
        if(!$data['attrs-tab1_header']) {
            $data['attrs-tab1_header'] = 'Применение';
        }

        parent::setData($data);
	}

    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('parent')->setOption('model', $this->getModel());
        $this->get('catalog_id')->setOption('model', $model->getPlugin('catalog'));
        $this->get('tags-collection')->setOption('model', $model->getPlugin('tags'));
        $this->get('types-collection')->setOption('model', $model->getPlugin('types'));

        $this->get('brand_id')->setOptions([
            'model' => $model->getPlugin('brand')
        ]);

        $this->get('image-image')->setOptions([
            'model' => $model->getPlugin('image'),
        ]);

        $this->get('images-images')->setOptions([
            'model'   => $model->getPlugin('images'),
            'product' => $model,
        ]);

        $this->add([
            'name' => 'attrs-prop_name_1',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Название свойства',
                'options' => [
                    ''      => 'Размер',
                ],
            ],
        ]);

        $this->add([
            'name' => 'attrs-prop_name_2',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Название свойства',
                'options' => [
                    ''      => 'Вкус',
                    'Цвет'  => 'Цвет',
                ],
            ],
        ]);

        $this->add([
            'name' => 'attrs-portion',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Размер порции',
            ],
            'attributes'=> [
                'placeholder' => 'шт. или граммы',
            ],
        ]);

        $this->add([
            'name' => 'sort',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [
                    4 => 'Очень высокий',
                    3 => 'Высокий',
                    2 => 'Средне',
                    1 => 'Низкий',
                    0 => 'Очень низкий',
                ],
                'label' => 'Приоритет закупки',
            ],
        ]);

        $this->get('size-collection')->setOption('model', $model->getPlugin('size'));

        $props = $model->getPlugin('props');
        $props->select()->order('prop_id');

        $catProps = new CatalogProps();
        $catProps->select()->where(['depend' => $model->get('catalog_id')]);

        $catProps2 = $catProps->getCollection(['clear' => false]);
        $catProps2->select()->where(['depend' => $model->get('catalog_id')]);

        $this->get('props-collection')->setOption('options', [
            'prop_id' => [
                'label'   => 'Группа',
                'width'   => 150,
                'sort'    => 'name',
                'options' => $catProps2
            ],
            'multiplier' => [
                'label'   => 'Расчет',
                'width'   => 110,
                'options' => [
                    0 => 'Без расчета',
                    1 => 'С расчетом',
                ]
            ],
            'compare' => [
                'label'   => 'Сравнение',
                'width'   => 80,
                'options' => [
                    0 => 'Выкл',
                    1 => 'Вкл',
                ]
            ],
            'key' => ['label'   => 'Параметр', 'width'   => 150],
            'val' => ['label'   => 'Значение', 'width'   => 270],
            'units' => [
                'label'   => '',
                'width'   => 80,
                'options' => [
                    ''     => '',
                    'кг'   => 'кг',
                    'г'    => 'г',
                    'мг'   => 'мг',
                    'мкг'  => 'мкг',
                    'кал'  => 'кал',
                    'ккал' => 'ккал',
                ]
            ],
        ]);
        
        $this->get('props-collection')->setOption('model', $props);

        $this->get('taste-collection')->setOption('model', $model->getPlugin('taste'));
        $this->get('articles-collection')->setOption('model', $model->getPlugin('articles'));

        $recommendedModel = $model->getPlugin('recommended');
        $this->get('recommended-collection')->setOption('model', $recommendedModel);
    }

    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add([
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ]);

        $this->add([
            'name' => 'parent',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Родительский товар',
                'empty'   => '',
                'sort'    => 'Name',
            ],
        ]);

        $this->add([
            'name' => 'units',
            'type'  => 'Zend\Form\Element\Select',
            'options' => [
                'label'   => 'Еденицы измерения',
                'options' => Products::$gUnits,
            ],
        ]);

        $this->add([
            'name' => 'articles-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'article_id' => [
                        'label'   => 'Статьи',
                        'width'   => 150,
                        'options' => new Article()
                    ],
                ]
            ],
        ]);

        $this->add([
            'name' => 'recommended-collection',
            'type' => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options' => [
                    'product_id' => [
                        'label'   => 'Рекомендованные товары',
                        'width'   => 150,
                        'sort'    => 'name',
                        'options' => new Products()
                    ],
                ]
            ],
        ]);

        $this->add([
            'name' => 'props-collection',
            'type' => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options' => [
                    'prop_id' => [
                        'label'   => 'Группа',
                        'width'   => 150,
                        'sort'    => 'name',
                        'options' => new CatalogProps()
                    ],
                    'multiplier' => [
                        'label'   => 'Расчет',
                        'width'   => 110,
                        'options' => [
                            0 => 'Без расчета',
                            1 => 'С расчетом',
                        ]
                    ],
                    'compare' => [
                        'label'   => 'Сравнение',
                        'width'   => 80,
                        'options' => [
                            0 => 'Выкл',
                            1 => 'Вкл',
                        ]
                    ],
                    'key' => ['label'   => 'Параметр', 'width'   => 150],
                    'val' => ['label'   => 'Значение', 'width'   => 270],
                    'units' => [
                        'label'   => '',
                        'width'   => 80,
                        'options' => [
                            ''     => '',
                            'кг'   => 'кг',
                            'г'    => 'г',
                            'мг'   => 'мг',
                            'мкг'  => 'мкг',
                            'кал'  => 'кал',
                            'ккал' => 'ккал',
                        ]
                    ],
                ]
            ],
        ]);

        $this->add([
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Название',
            ],
        ]);

        $this->add([
            'name' => 'subname',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Название (2 строка)',
            ],
        ]);

        $this->add([
            'name' => 'short_name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Короткое название',
            ],
        ]);

        $this->add([
            'name' => 'sync_id',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Myprotein Spb ID',
            ],
        ]);

        $this->add([
            'name' => 'discount',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Скидка в процентах',
            ],
        ]);

        $this->add([
            'name' => 'type',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Тип',
            ],
        ]);

        $this->add([
            'name' => 'barcode',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Штрихкод',
            ],
        ]);

        $this->add([
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Url',
            ],
        ]);

        $this->add([
            'name' => 'attrs-mp_url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Myprotein Url',
            ],
        ]);

        $this->add([
            'name' => 'catalog_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Категория'
            ],
        ]);

        $types = CatalogTypes::getEntityCollection();
        $types->select()
            ->columns(['id', 'name' => new Expression('CONCAT(c.name, " - ", t.name)')])
            ->join(['c' => 'catalog'], 't.depend = c.id', [])
            ->order('c.name, t.name');

        $this->add([
            'name' => 'types-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'type_id' => [
                        'label'   => 'Тип',
                        'width'   => 200,
                        'options' => $types
                    ],
                ]
            ],
        ]);

        $this->add([
            'name' => 'tags-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options' => [
                    'name'     => ['label' => 'Тег', 'width' => 150],
                ],
                'label'   => 'Категория'
            ],
        ]);

        $this->add([
            'name' => 'brand_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => [
                'label'   => 'Производитель'
            ],
        ]);

        $this->add([
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Заголовок (Title)'
            ],
        ]);

        $this->add([
            'name' => 'preview',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => [
                'label' => 'Краткое описание'
            ],
        ]);

        $this->add([
            'name' => 'attrs-video',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'YouTube'
            ],
        ]);

        $form = $this;
        $addTabs = function($prefix) use ($form) {
            $this->addMeta($prefix);
            $form->add([
                'name' => $prefix . 'url',
                'type'  => 'Zend\Form\Element\Text',
                'options' => [
                    'label' => 'URL'
                ],
            ]);

            $form->add([
                'name' => $prefix . 'header',
                'type'  => 'Zend\Form\Element\Text',
                'options' => [
                    'label' => 'Заголовок'
                ],
            ]);

            $form->add([
                'name' => $prefix . 'text',
                'type'  => 'Zend\Form\Element\Textarea',
                'attributes'=> [
                    'class' => 'editor',
                    'id'    => 'page-text'
                ],
            ]);
        };

        $addTabs('tab1_');
        $addTabs('attrs-tab1_');
        $addTabs('attrs-tab2_');
        $addTabs('attrs-tab3_');

        $this->add([
            'name' => 'keywords',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Ключевые слова (Keywords)'
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => [
                'label' => 'Описание (Description)'
            ],
        ]);

        $this->add([
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => [],
        ]);

        $this->add([
            'name' => 'certificate-file',
            'type'  => 'Aptero\Form\Element\Admin\File',
            'options' => [],
        ]);

        $this->add([
            'name' => 'images-images',
            'type'  => 'Aptero\Form\Element\Admin\ProductImages',
            'options' => [],
        ]);

        $this->add([
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=> [
                'class' => 'editor',
                'id'    => 'page-text'
            ],
        ]);

        $this->add([
            'name' => 'taste-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'name'     => ['label' => 'Вкус', 'width' => 150],
                    'coefficient'   => 'Коэфициент',
                ]
            ],
        ]);

        $this->add([
            'name' => 'size-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'name'     => ['label' => 'Название', 'width' => 150],
                    'size'     => ['label' => 'Размер', 'width' => 150],
                    'price'    => ['label' => 'Стоимость', 'width' => 150],
                    'weight'   => ['label' => 'Вес', 'width' => 50],
                ]
            ],
        ]);
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-prop_name_1',
            'required' => false,
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'attrs-prop_name_2',
            'required' => false,
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}