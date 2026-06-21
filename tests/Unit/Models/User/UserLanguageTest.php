<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\User;

use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UserLanguage;
use Engelsystem\Test\Unit\Models\ModelTest;

class UserLanguageTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\User\UserLanguage
     */
    public function testCreate(): void
    {
        $user = User::factory()->create();

        $language = UserLanguage::create([
            'user_id' => $user->id,
            'language_code' => 'de',
            'is_native' => true,
        ]);

        $this->assertNotNull($language->id);
        $this->assertEquals('de', $language->language_code);
        $this->assertTrue($language->is_native);

        // Verify it was saved to database
        $loaded = UserLanguage::find($language->id);
        $this->assertNotNull($loaded);
        $this->assertEquals($user->id, $loaded->user_id);
        $this->assertEquals('de', $loaded->language_code);
        $this->assertTrue($loaded->is_native);
    }

    /**
     * @covers \Engelsystem\Models\User\UserLanguage
     */
    public function testUserRelationship(): void
    {
        $user = User::factory()->create();
        $language = UserLanguage::factory()->create([
            'user_id' => $user->id,
            'language_code' => 'en',
        ]);

        $this->assertEquals($user->id, $language->user->id);
    }

    /**
     * @covers \Engelsystem\Models\User\UserLanguage
     */
    public function testUserLanguagesRelationship(): void
    {
        $user = User::factory()->create();

        UserLanguage::factory()->create([
            'user_id' => $user->id,
            'language_code' => 'en',
            'is_native' => true,
        ]);

        UserLanguage::factory()->create([
            'user_id' => $user->id,
            'language_code' => 'de',
            'is_native' => false,
        ]);

        $user->refresh();

        $this->assertCount(2, $user->languages);
        $this->assertEquals('en', $user->languages->firstWhere('language_code', 'en')->language_code);
        $this->assertTrue($user->languages->firstWhere('language_code', 'en')->is_native);
        $this->assertFalse($user->languages->firstWhere('language_code', 'de')->is_native);
    }

    /**
     * @covers \Engelsystem\Models\User\UserLanguage
     */
    public function testDefaults(): void
    {
        $user = User::factory()->create();

        $language = UserLanguage::create([
            'user_id' => $user->id,
            'language_code' => 'fr',
        ]);

        $this->assertFalse($language->is_native);
    }
}
