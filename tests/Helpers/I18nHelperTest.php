<?php

namespace Tests\Helpers;

use App\Helpers\I18nHelper;
use App\Models\V2\I18n\I18nItem;
use App\Models\V2\Forms\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class I18nHelperTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    public function it_should_generate_i18n_item_when_no_i18n_relationship()
    {
        $target = Form::factory()->create([
            'title' => 'mock_title',
            'title_id' => null
        ]);

        $shouldGenerateI18nItem = I18nHelper::shouldGenerateI18nItem($target, 'title');
        $this->assertTrue($shouldGenerateI18nItem);
    }

    /** @test */
    public function it_should_generate_i18n_item_when_i18n_is_not_found()
    {
        $target = Form::factory()->create([
            'title' => 'mock_title',
            'title_id' => 1
        ]);

        $shouldGenerateI18nItem = I18nHelper::shouldGenerateI18nItem($target, 'title');
        $this->assertTrue($shouldGenerateI18nItem);
    }

    /** @test */
    public function it_should_generate_i18n_item_when_i18n_type_is_different()
    {
        $i18nItem = I18nItem::factory()->create([
            'short_value' => 'Short string',
            'type' => 'short'
        ]);
        $target = Form::factory()->create([
            'title' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec odio Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec odio Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec odio Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec odio.',
            'title_id' => $i18nItem->id
        ]);

        $shouldGenerateI18nItem = I18nHelper::shouldGenerateI18nItem($target, 'title');
        $this->assertTrue($shouldGenerateI18nItem);
    }

    /** @test */
    public function it_should_generate_i18n_item_when_i18n_value_is_different()
    {
        $i18nItem = I18nItem::factory()->create([
            'short_value' => 'Short string',
            'type' => 'short'
        ]);
        $target = Form::factory()->create([
            'title' => 'New short string',
            'title_id' => $i18nItem->id
        ]);

        $shouldGenerateI18nItem = I18nHelper::shouldGenerateI18nItem($target, 'title');
        $this->assertTrue($shouldGenerateI18nItem);
    }

    /** @test */
    public function it_should_not_generate_i18n_item_when_i18n_value_is_same()
    {
        $i18nItem = I18nItem::factory()->create([
            'short_value' => 'Short string',
            'type' => 'short'
        ]);
        $target = Form::factory()->create([
            'title' => 'Short string',
            'title_id' => $i18nItem->id
        ]);

        $shouldGenerateI18nItem = I18nHelper::shouldGenerateI18nItem($target, 'title');
        $this->assertFalse($shouldGenerateI18nItem);
    }

    /** @test */
    public function it_should_generate_i18n_item_when_no_i18n_relationship_id()
    {
        $i18nItemCountBefore = I18nItem::count();

        $target = Form::factory()->create([
            'title' => 'mock_title',
            'title_id' => null
        ]);

        $i18nItemId = I18nHelper::generateI18nItem($target, 'title');
        $i18nItemCountAfter = I18nItem::count();

        $this->assertNotNull($i18nItemId);
        $this->assertEquals($i18nItemCountBefore + 1, $i18nItemCountAfter);
    }

    /** @test */
    public function it_should_not_generate_and_return_id_when_id_is_in_place()
    {
        $i18nItem = I18nItem::factory()->create([
            'short_value' => 'mock_title',
            'type' => 'short'
        ]);
        $i18nItemCountBefore = I18nItem::count();

        $initialI18nItemId = $i18nItem->id;

        $target = Form::factory()->create([
            'title' => 'mock_title',
            'title_id' => $initialI18nItemId
        ]);

        $i18nItemId = I18nHelper::generateI18nItem($target, 'title');
        $i18nItemCountAfter = I18nItem::count();

        $this->assertEquals($i18nItemId, $initialI18nItemId);
        $this->assertEquals($i18nItemCountBefore, $i18nItemCountAfter);
    }

}