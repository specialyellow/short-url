<?php

namespace SpecialYellow\ShortURL\Tests\Unit\Classes;

use SpecialYellow\ShortURL\Classes\Builder;
use SpecialYellow\ShortURL\Exceptions\ShortURLException;
use SpecialYellow\ShortURL\Exceptions\ValidationException;
use SpecialYellow\ShortURL\Models\ShortURL;
use SpecialYellow\ShortURL\Tests\Unit\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use ShortURL as ShortURLAlias;

class BuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('short-url.default_url', 'https://short-url.com');
        Config::set('app.url', 'https://app-url.com');
    }

    /** @test */
    public function exception_is_thrown_in_the_constructor_if_the_config_variables_are_invalid()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The config URL length is not a valid integer.');

        Config::set('short-url.key_length', 'INVALID');

        new Builder();
    }

    /** @test */
    public function exception_is_thrown_if_the_destination_url_does_not_begin_with_http_or_https()
    {
        $this->expectException(ShortURLException::class);
        $this->expectExceptionMessage('The destination URL must begin with http:// or https://');

        $builder = new Builder();
        $builder->destinationUrl('INVALID');
    }

    /** @test */
    public function exception_is_thrown_if_no_destination_url_is_set()
    {
        $this->expectException(ShortURLException::class);
        $this->expectExceptionMessage('No destination URL has been set.');

        $builder = new Builder();
        $builder->make();
    }

    /** @test */
    public function destination_url_is_changed_to_https_if_secure_flag_has_been_set()
    {
        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->secure()->make();
        $this->assertSame('https://domain.com', $shortUrl->destination_url);
    }

    /** @test */
    public function destination_url_is_not_changed_to_https_if_secure_flag_has_been_set_to_false()
    {
        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->secure(false)->make();
        $this->assertSame('http://domain.com', $shortUrl->destination_url);
    }

    /** @test */
    public function destination_url_is_changed_to_https_if_enforce_https_flag_is_set_to_true_from_the_config()
    {
        Config::set('short-url.enforce_https', true);
        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->make();
        $this->assertSame('https://domain.com', $shortUrl->destination_url);
    }

    /** @test */
    public function destination_url_is_not_changed_to_https_if_enforce_https_flag_is_set_to_false_from_the_config()
    {
        Config::set('short-url.enforce_https', false);
        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->make();
        $this->assertSame('http://domain.com', $shortUrl->destination_url);
    }

    /** @test */
    public function destination_url_is_changed_to_https_if_enforce_https_flag_is_set_to_false_in_the_config_but_set_when_creating_url()
    {
        Config::set('short-url.enforce_https', false);
        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->secure()->make();
        $this->assertSame('https://domain.com', $shortUrl->destination_url);
    }

    /** @test */
    public function forward_query_params_is_set_from_the_config_if_it_is_not_explicitly_set()
    {
        Config::set('short-url.forward_query_params', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->make();
        $this->assertTrue($shortUrl->forward_query_params);

        Config::set('short-url.forward_query_params', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->make();
        $this->assertFalse($shortUrl->forward_query_params);
    }

    /** @test */
    public function forward_query_params_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.forward_query_params', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->forwardQueryParams(false)->make();
        $this->assertFalse($shortUrl->forward_query_params);

        Config::set('short-url.forward_query_params', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->forwardQueryParams()->make();
        $this->assertTrue($shortUrl->forward_query_params);
    }

    /** @test */
    public function track_visits_flag_is_set_from_the_config_if_it_is_not_explicitly_set()
    {
        Config::set('short-url.tracking.default_enabled', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->make();
        $this->assertTrue($shortUrl->track_visits);

        Config::set('short-url.tracking.default_enabled', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->make();
        $this->assertFalse($shortUrl->track_visits);
    }

    /** @test */
    public function track_visits_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.default_enabled', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackVisits(false)->make();
        $this->assertFalse($shortUrl->track_visits);

        Config::set('short-url.tracking.default_enabled', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackVisits()->make();
        $this->assertTrue($shortUrl->track_visits);
    }

    /** @test */
    public function track_ip_address_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.fields.ip_address', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackIPAddress(false)->make();
        $this->assertFalse($shortUrl->track_ip_address);

        Config::set('short-url.tracking.fields.ip_address', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackIPAddress()->make();
        $this->assertTrue($shortUrl->track_ip_address);
    }

    /** @test */
    public function track_browser_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.fields.browser', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackBrowser(false)->make();
        $this->assertFalse($shortUrl->track_browser);

        Config::set('short-url.tracking.fields.browser', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackBrowser()->make();
        $this->assertTrue($shortUrl->track_browser);
    }

    /** @test */
    public function track_browser_version_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.fields.browser_version', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackBrowserVersion(false)->make();
        $this->assertFalse($shortUrl->track_browser_version);

        Config::set('short-url.tracking.fields.browser_version', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackBrowserVersion()->make();
        $this->assertTrue($shortUrl->track_browser_version);
    }

    /** @test */
    public function track_operating_system_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.fields.operating_system', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackOperatingSystem(false)->make();
        $this->assertFalse($shortUrl->track_operating_system);

        Config::set('short-url.tracking.fields.operating_system', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackOperatingSystem()->make();
        $this->assertTrue($shortUrl->track_operating_system);
    }

    /** @test */
    public function track_operating_system_version_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.fields.operating_system_version', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackOperatingSystemVersion(false)->make();
        $this->assertFalse($shortUrl->track_operating_system_version);

        Config::set('short-url.tracking.fields.operating_system_version', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackOperatingSystemVersion()->make();
        $this->assertTrue($shortUrl->track_operating_system_version);
    }

    /** @test */
    public function track_referer_url_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.fields.referer_url', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackRefererURL(false)->make();
        $this->assertFalse($shortUrl->track_referer_url);

        Config::set('short-url.tracking.fields.referer_url', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackRefererURL()->make();
        $this->assertTrue($shortUrl->track_referer_url);
    }

    /** @test */
    public function track_device_type_flag_is_not_set_from_the_config_if_it_is_explicitly_set()
    {
        Config::set('short-url.tracking.fields.device_type', true);

        $builder = new Builder();
        $shortUrl = $builder->destinationUrl('http://domain.com')->trackDeviceType(false)->make();
        $this->assertFalse($shortUrl->track_device_type);

        Config::set('short-url.tracking.fields.device_type', false);

        $shortUrl = $builder->destinationUrl('http://domain.com')->trackDeviceType()->make();
        $this->assertTrue($shortUrl->track_device_type);
    }

    /** @test */
    public function exception_is_thrown_if_the_url_key_is_explicitly_set_and_already_exists_in_the_db()
    {
        ShortURL::create([
            'default_short_url' => 'https://short.com/urlkey123',
            'destination_url'   => 'https://destination.com/SpecialYellow',
            'url_key'           => 'urlkey123',
            'single_use'        => false,
            'track_visits'      => false,
        ]);

        $this->expectException(ShortURLException::class);
        $this->expectExceptionMessage('A short URL with this key already exists.');

        $builder = new Builder();
        $builder->destinationUrl('https://domain.com')->urlKey('urlkey123')->make();
    }

    /** @test */
    public function explicitly_defined_url_key_can_be_used_if_it_does_not_exist_in_the_db()
    {
        $builder = new Builder();
        $builder->destinationUrl('https://domain.com')->urlKey('urlkey123')->make();

        $this->assertDatabaseHas('short_urls', ['url_key' => 'urlkey123']);
    }

    /** @test */
    public function random_url_key_is_generated_if_one_is_not_explicitly_defined()
    {
        $builder = new Builder();
        $shortURL = $builder->destinationUrl('https://domain.com')->make();

        $this->assertNotNull($shortURL->url_key);
        $this->assertSame(5, strlen($shortURL->url_key));
    }

    /** @test */
    public function short_url_can_be_created_and_stored_in_the_database()
    {
        $builder = new Builder();
        $shortURL = $builder->destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->secure()
            ->trackVisits(false)
            ->trackDeviceType(true)
            ->trackRefererURL(false)
            ->trackBrowser(true)
            ->trackOperatingSystemVersion(false)
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url'              => 'https://short-url.com/short/customKey',
            'url_key'                        => 'customKey',
            'destination_url'                => 'https://domain.com',
            'track_visits'                   => false,
            'single_use'                     => false,
            'redirect_status_code'           => 301,
            'track_ip_address'               => true,
            'track_operating_system'         => true,
            'track_operating_system_version' => false,
            'track_browser'                  => true,
            'track_browser_version'          => true,
            'track_referer_url'              => false,
            'track_device_type'              => true,
            'activated_at'                   => now(),
            'deactivated_at'                 => null,
        ]);
    }

    /** @test */
    public function short_url_can_be_created_and_stored_in_the_database_using_the_facade()
    {
        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->secure()
            ->trackVisits(false)
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url'    => 'https://short-url.com/short/customKey',
            'url_key'              => 'customKey',
            'destination_url'      => 'https://domain.com',
            'track_visits'         => false,
            'single_use'           => false,
            'redirect_status_code' => 301,
        ]);
    }

    /** @test */
    public function correct_redirect_status_code_is_stored_if_explicitly_set()
    {
        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->secure()
            ->trackVisits(false)
            ->redirectStatusCode(302)
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url'    => 'https://short-url.com/short/customKey',
            'url_key'              => 'customKey',
            'destination_url'      => 'https://domain.com',
            'track_visits'         => false,
            'single_use'           => false,
            'redirect_status_code' => 302,
        ]);
    }

    /** @test */
    public function exception_is_thrown_if_the_redirect_status_code_is_not_valid()
    {
        $this->expectException(ShortURLException::class);
        $this->expectExceptionMessage('The redirect status code must be a valid redirect HTTP status code.');

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->secure()
            ->trackVisits(false)
            ->redirectStatusCode(-100)
            ->make();

        $this->assertDatabaseMissing('short_urls', [
            'destination_url' => 'https://domain.com',
        ]);
    }

    /** @test */
    public function exception_is_thrown_if_the_activation_date_is_in_the_past()
    {
        $this->expectException(ShortURLException::class);
        $this->expectExceptionMessage('The activation date must not be in the past.');

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->activateAt(now()->subHour())
            ->make();
    }

    /** @test */
    public function exception_is_thrown_if_the_deactivation_date_is_in_the_past()
    {
        $this->expectException(ShortURLException::class);
        $this->expectExceptionMessage('The deactivation date must not be in the past.');

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->deactivateAt(now()->subHour())
            ->make();
    }

    /** @test */
    public function exception_is_thrown_if_the_deactivation_date_is_before_the_activation_date()
    {
        $this->expectException(ShortURLException::class);
        $this->expectExceptionMessage('The deactivation date must not be before the activation date.');

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->activateAt(now()->addHour())
            ->deactivateAt(now()->addMinute())
            ->make();
    }

    /** @test */
    public function short_url_can_be_created_with_an_explicit_activation_date()
    {
        $activateTime = now()->addHour();

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->activateAt($activateTime)
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url' => 'https://short-url.com/short/customKey',
            'url_key'           => 'customKey',
            'activated_at'      => $activateTime->format('Y-m-d H:i:s'),
            'deactivated_at'    => null,
        ]);
    }

    /** @test */
    public function short_url_can_be_created_with_an_explicit_activation_date_and_deactivation_date()
    {
        $activateTime = now()->addHour();
        $deactivateTime = now()->addHours(2);

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->activateAt($activateTime)
            ->deactivateAt($deactivateTime)
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url' => 'https://short-url.com/short/customKey',
            'url_key'           => 'customKey',
            'activated_at'      => $activateTime->format('Y-m-d H:i:s'),
            'deactivated_at'    => $deactivateTime->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function short_url_can_be_created_with_an_explicit_deactivation_date()
    {
        $deactivateTime = now()->addHours(2);

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->deactivateAt($deactivateTime)
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url' => 'https://short-url.com/short/customKey',
            'url_key'           => 'customKey',
            'activated_at'      => now(),
            'deactivated_at'    => $deactivateTime->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function short_url_prefix_can_be_changed_via_configuration()
    {
        Config::set('short-url.prefix', '/s');

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url' => 'https://short-url.com/s/customKey',
        ]);
    }

    /**
     * @test
     *
     * @testWith ["s", "s"]
     *           ["/s", "s"]
     *           ["/s/", "s"]
     *           ["s/", "s"]
     *           [null, null]
     */
    public function correct_prefix_is_returned(?string $prefix, ?string $expected)
    {
        Config::set('short-url.prefix', $prefix);

        self::assertSame($expected, ShortURLAlias::prefix());
    }

    /** @test */
    public function short_url_can_be_created_with_a_null_prefix(): void
    {
        $deactivateTime = now()->addHours(2);

        Config::set('short-url.prefix', null);

        ShortURLAlias::destinationUrl('http://domain.com')
            ->urlKey('customKey')
            ->deactivateAt($deactivateTime)
            ->make();

        $this->assertDatabaseHas('short_urls', [
            'default_short_url' => 'https://short-url.com/customKey',
            'url_key' => 'customKey',
        ]);
    }

    /**
     * @test
     *
     * @testWith [true, "https://domain.com"]
     *           [false, "https://fallback.com"]
     */
    public function data_can_be_set_on_the_builder_using_when(bool $flag, string $destination): void
    {
        $shortUrl = (new Builder())
            ->when(
                $flag,
                fn (Builder $builder): Builder => $builder->destinationUrl('https://domain.com'),
                fn (Builder $builder): Builder => $builder->destinationUrl('https://fallback.com')
            )
            ->make();

        $this->assertSame($destination, $shortUrl->destination_url);
    }

    /** @test */
    public function app_url_is_set_if_the_default_url_config_value_is_not_set(): void
    {
        Config::set('short-url.default_url', null);

        $shortUrl = (new Builder())
            ->destinationUrl('https://domain.com')
            ->urlKey('abc123')
            ->make();

        $this->assertSame('https://app-url.com/short/abc123', $shortUrl->default_short_url);
    }

    /** @test */
    public function short_url_can_be_created_with_a_custom_integer_seed(): void
    {
        $shortUrlOne = (new Builder())
            ->destinationUrl('https://domain.com')
            ->generateKeyUsing(123)
            ->make();

        $this->assertSame('https://short-url.com/short/4ZRw4', $shortUrlOne->default_short_url);
    }

    /** @test */
    public function short_url_can_be_created_using_the_url_key_if_the_key_and_seeder_are_both_set(): void
    {
        $shortUrl = (new Builder())
            ->destinationUrl('https://domain.com')
            ->generateKeyUsing(111111)
            ->urlKey('abc123')
            ->make();

        $this->assertSame('https://short-url.com/short/abc123', $shortUrl->default_short_url);
    }
}
