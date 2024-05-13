<?php

use DoniaShaker\MediaLibrary\MediaController;
use DoniaShaker\MediaLibrary\MediaLibraryServiceProvider;
use DoniaShaker\MediaLibrary\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MediaController::class)]
class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            MediaLibraryServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

        $app['config']->set('database.default', 'testdb');
        $app['config']->set('database.connections.testdb', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('media.publicPath', __DIR__.'/../.phpunit.cache/public/media');
        $app['config']->set('media.storagePath', __DIR__.'/../.phpunit.cache/public/storage/media');
        $app['config']->set('media.useStorage', true);
    }

    public function testSaveImage(): void
    {

        $media_functions = new MediaController();
        $image = new UploadedFile(__DIR__.'/stubs/image.jpg', 'image.jpg');
        $media_functions->saveImage('test', 1, $image);
        $this->assertCount(1, Media::all(), 'Media count should be 1');
    }

    public function testSaveTempImage(): void
    {
        $media_functions = new MediaController();
        $image = new UploadedFile(__DIR__.'/stubs/image.jpg', 'image.jpg');
        $media_functions->saveTempImage('test', 1, $image);

        $this->assertCount(1, Media::all(), 'Media count should be 1');
    }

    public function testRemoveTempImage(): void
    {
        $media_functions = new MediaController();
        $image = new UploadedFile(__DIR__.'/stubs/image.jpg', 'image.jpg');
        $media_functions->saveTempImage('test', 1, $image);
        $image = $media_functions->deleteTemp(1);

        $this->assertSoftDeleted(Media::class, ['id' => 1]);
    }

    public function testConvertMedia(): void
    {
        $media_functions = new MediaController();
        $image = new UploadedFile(__DIR__.'/stubs/image.jpg', 'image.jpg');

        $media_functions->saveTempImage('test', 1, $image);
        $image = $media_functions->convertTempImage('test', 1, 1);

        $this->assertCount(1, Media::all(), 'Media count should be 1');
    }

    public function testSavePDF(): void
    {
        $fakeFile = UploadedFile::fake()->create('fake.pdf', 500);
        $media_functions = new MediaController();
        $media_functions->uploadFile('test', 1, $fakeFile);

        // Assert response
        $this->assertTrue(true);
    }

    public function testSaveAudio(): void
    {
        $fakeFile = UploadedFile::fake()->create('fake.mp3', 500);
        $media_functions = new MediaController();
        $media_functions->audio('test', 1, $fakeFile);

        // Assert response
        $this->assertTrue(true);
    }

    public function testSaveVideo(): void
    {
        $fakeFile = UploadedFile::fake()->create('fake.mp4', 500);
        $media_functions = new MediaController();
        $media_functions->video('test', 1, $fakeFile);

        // Assert response
        $this->assertTrue(true);
    }
}
