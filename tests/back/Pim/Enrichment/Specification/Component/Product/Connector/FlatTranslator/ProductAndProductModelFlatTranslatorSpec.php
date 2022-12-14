<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValuesFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\FlatHeaderTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\ProductAndProductModelFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AssociationTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValueRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAndProductModelFlatTranslatorSpec extends ObjectBehavior
{
    function let(
        HeaderRegistry $headerRegistry,
        PropertyValueRegistry $propertyValueRegistry,
        AttributeValuesFlatTranslator $attributeValuesFlatTranslator,
        AssociationTranslator $associationTranslator
    ) {
        $this->beConstructedWith(
            $headerRegistry,
            $propertyValueRegistry,
            $attributeValuesFlatTranslator,
            $associationTranslator
        );
    }

    function it_is_initializable() {
        $this->shouldHaveType(ProductAndProductModelFlatTranslator::class);
    }

    function it_translates_a_product(
        FlatHeaderTranslatorInterface $headerTranslator,
        HeaderRegistry $headerRegistry,
        AttributeValuesFlatTranslator $attributeValuesFlatTranslator,
        AssociationTranslator $associationTranslator
    ) {
        $headerRegistry->warmup(['sku', 'categories', 'description-en_US', 'enabled', 'groups', 'name-fr_FR', 'UP_SELL-product_models', 'UP_SELL-products', 'collection'], 'fr_FR')->shouldBeCalled();
        $headerRegistry->getTranslator(Argument::any())->willReturn($headerTranslator);
        $headerRegistry->getTranslator('sku')->willReturn(null);

        $headerTranslator->translate('sku', 'fr_FR')->shouldNotBeCalled();
        $headerTranslator->translate('categories', 'fr_FR')->willReturn('Cat??gories');
        $headerTranslator->translate('description-en_US', 'fr_FR')->willReturn('Description (Anglais Am??ricain)');
        $headerTranslator->translate('enabled', 'fr_FR')->willReturn('Activ??');
        $headerTranslator->translate('collection', 'fr_FR')->willReturn('Collection');
        $headerTranslator->translate('groups', 'fr_FR')->willReturn('Groupes');
        $headerTranslator->translate('name-fr_FR', 'fr_FR')->willReturn('Nom (Fran??ais Fran??ais)');
        $headerTranslator->translate('UP_SELL-products', 'fr_FR')->willReturn('Proposition achat crois??s de produits');
        $headerTranslator->translate('UP_SELL-product_models', 'fr_FR')->willReturn('Proposition achat crois??s de mod??les');

        $associationTranslator->supports('UP_SELL-products')->willReturn(true);
        $associationTranslator->supports('UP_SELL-product_models')->willReturn(true);

        $associationTranslator->translate('UP_SELL-products', ['scarf,watch', 'scarf,watch'], 'fr_FR', 'ecommerce')
            ->willReturn(['Belle ??charpe,Belle montre', 'Belle ??charpe,Belle montre']);
        $associationTranslator->translate('UP_SELL-product_models', ['tshirt-xs,hat', 'tshirt-xs,hat'], 'fr_FR', 'ecommerce')
            ->willReturn(['Beau T-shirt,Beau chapeau', 'Beau T-shirt,Beau chapeau']);

        $attributeValuesFlatTranslator->supports('sku')->willReturn(false);
        $attributeValuesFlatTranslator->supports('categories')->willReturn(false);
        $attributeValuesFlatTranslator->supports('description-en_US')->willReturn(false);
        $attributeValuesFlatTranslator->supports('name-fr_FR')->willReturn(false);
        $attributeValuesFlatTranslator->supports('collection')->willReturn(false);
        $attributeValuesFlatTranslator->supports('enabled')->willReturn(false);
        $attributeValuesFlatTranslator->supports('groups')->willReturn(false);
        $attributeValuesFlatTranslator->supports('UP_SELL-products')->willReturn(false);
        $attributeValuesFlatTranslator->supports('UP_SELL-product_models')->willReturn(false);
        $associationTranslator->supports('sku')->willReturn(false);
        $associationTranslator->supports('categories')->willReturn(false);
        $associationTranslator->supports('description-en_US')->willReturn(false);
        $associationTranslator->supports('name-fr_FR')->willReturn(false);
        $associationTranslator->supports('collection')->willReturn(false);
        $associationTranslator->supports('enabled')->willReturn(false);
        $associationTranslator->supports('groups')->willReturn(false);

        $this->translate([
            [
                'sku'                    => 1151511,
                'categories'             => 'master_femme_chaussures_sandales',
                'description-en_US'      => 'Ma description',
                'enabled'                => 0,
                'groups'                 => 'group1',
                'name-fr_FR'             => 'Sandales dor??es Femme',
                'UP_SELL-product_models' => 'tshirt-xs,hat',
                'UP_SELL-products'       => 'scarf,watch'
            ],
            [
                'sku'                    => 1151512,
                'categories'             => 'master_femme_manteaux_manteaux_dhiver',
                'description-en_US'      => 'Ma description1',
                'enabled'                => 1,
                'groups'                 => 'group2,group3',
                'name-fr_FR'             => 'Jupe imprim??e Femme',
                'collection'             => 'summer_2016',
                'UP_SELL-product_models' => 'tshirt-xs,hat',
                'UP_SELL-products'       => 'scarf,watch'
            ]
        ], 'fr_FR', 'ecommerce', true)
            ->shouldReturn(
                [
                    [
                        'sku--[sku]'                                 => 1151511,
                        'categories--Cat??gories'                            => 'master_femme_chaussures_sandales',
                        'description-en_US--Description (Anglais Am??ricain)'       => 'Ma description',
                        'enabled--Activ??'                                => 0,
                        'groups--Groupes'                               => 'group1',
                        'name-fr_FR--Nom (Fran??ais Fran??ais)'               => 'Sandales dor??es Femme',
                        'UP_SELL-product_models--Proposition achat crois??s de mod??les'  => 'Beau T-shirt,Beau chapeau',
                        'UP_SELL-products--Proposition achat crois??s de produits' => 'Belle ??charpe,Belle montre'
                    ],
                    [
                        'sku--[sku]'                                 => 1151512,
                        'categories--Cat??gories'                            => 'master_femme_manteaux_manteaux_dhiver',
                        'description-en_US--Description (Anglais Am??ricain)'       => 'Ma description1',
                        'enabled--Activ??'                                => 1,
                        'groups--Groupes'                               => 'group2,group3',
                        'name-fr_FR--Nom (Fran??ais Fran??ais)'               => 'Jupe imprim??e Femme',
                        'UP_SELL-product_models--Proposition achat crois??s de mod??les'  => 'Beau T-shirt,Beau chapeau',
                        'UP_SELL-products--Proposition achat crois??s de produits' => 'Belle ??charpe,Belle montre',
                        'collection--Collection'                            => 'summer_2016'
                    ]
                ]
            );
    }
}
