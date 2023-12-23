<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\Faq;
use Engelsystem\Models\Tag;

class TagTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Tag::faqs
     */
    public function testFaqs(): void
    {
        /** @var Faq $faq1 */
        $faq1 = Faq::factory()->create();
        /** @var Faq $faq2 */
        $faq2 = Faq::factory()->create();

        $model = new Tag();
        $model->name = 'Some Tag';
        $model->save();

        $model->faqs()->attach($faq1);
        $model->faqs()->attach($faq2);

        /** @var Tag $savedModel */
        $savedModel = Tag::all()->last();
        $this->assertEquals($faq1->question, $savedModel->faqs[0]->question);
        $this->assertEquals($faq2->question, $savedModel->faqs[1]->question);
    }
}
