<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ReindexFormerAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenRemovedFromVariantProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ReindexFormerAncestorsSubscriberSpec extends ObjectBehavior
{
    function let(
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        ProductModelIndexerInterface $productModelIndexer
    ) {
        $this->beConstructedWith($getAncestorAndDescendantProductModelCodes, $productModelIndexer);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReindexFormerAncestorsSubscriber::class);
    }

    function it_does_not_reindex_when_saving_non_products(
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        ProductModelIndexerInterface $productModelIndexer
    ) {
        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(Argument::any())->shouldNotBeCalled();
        $productModelIndexer->indexFromProductModelCodes(Argument::any())->shouldNotBeCalled();

        $this->reIndex(new GenericEvent(new \stdClass(), ['unitary' => false]));
    }

    function it_does_not_reindex_on_post_save_for_a_non_unitary_save_event(
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        ProductModelIndexerInterface $productModelIndexer,
        ProductInterface $product
    ) {
        $product->getUuid()->willReturn(Uuid::uuid4());
        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(Argument::any())->shouldNotBeCalled();
        $productModelIndexer->indexFromProductModelCodes(Argument::any())->shouldNotBeCalled();

        $this->store(new ParentHasBeenRemovedFromVariantProduct($product->getWrappedObject(), 'parent_model_code'));
        $this->reIndex(new GenericEvent($product->getWrappedObject(), ['unitary' => false]));
    }

    function it_reindexes_the_former_ancestor_models_of_a_product(
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        ProductModelIndexerInterface $productModelIndexer,
        ProductInterface $product
    ) {
        $product->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $this->store(new ParentHasBeenRemovedFromVariantProduct($product->getWrappedObject(), 'parent_model_code'));

        $getAncestorAndDescendantProductModelCodes
            ->fromProductModelCodes(['parent_model_code'])
            ->shouldBeCalled()
            ->willReturn(['root_model_code']);
        $productModelIndexer->indexFromProductModelCodes(['parent_model_code', 'root_model_code'])
            ->shouldBeCalled();

        $this->reIndex(new GenericEvent($product->getWrappedObject(), ['unitary' => true]));
    }

    function it_mass_reindexes_former_ancestor_of_products(
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        ProductModelIndexerInterface $productModelIndexer,
        ProductInterface $productToReindex,
        ProductInterface $ignoredProduct,
        ProductInterface $otherProductToReindex
    ) {
        $productToReindex->getUuid()->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $ignoredProduct->getUuid()->willReturn(Uuid::fromString('df31ba3f-508d-424c-8bc4-446c6e2966e5'));
        $otherProductToReindex->getUuid()->willReturn(Uuid::fromString('fdf6f091-3f75-418f-98af-8c19db8b0000'));

        $this->store(
            new ParentHasBeenRemovedFromVariantProduct(
                $productToReindex->getWrappedObject(), 'model_tshirt'
            )
        );
        $this->store(
            new ParentHasBeenRemovedFromVariantProduct(
                $otherProductToReindex->getWrappedObject(), 'model_shoes'
            )
        );

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['model_tshirt', 'model_shoes'])
            ->shouldBeCalled()->willReturn(['root_model_shoes']);
        $productModelIndexer->indexFromProductModelCodes(['model_tshirt', 'model_shoes', 'root_model_shoes'])
            ->shouldBeCalled();

        $this->reIndexAll(
            new GenericEvent(
                [
                    $productToReindex->getWrappedObject(),
                    $ignoredProduct->getWrappedObject(),
                    $otherProductToReindex->getWrappedObject(),
                ]
            )
        );
    }
}
