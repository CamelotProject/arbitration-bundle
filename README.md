Camelot Arbitration
===================

Arbitration is an image manipulation library base upon [Intervention Image](http://image.intervention.io/). It started
as a fork of [`league/glide`](http://glide.thephpleague.com/).

Features
--------

- Dynamic generation of a set of manipulated images & image set (`srcset`)
- Cache generated files on the filesystem for serving directly
- Command line tools for managing render cache
- HTML `img` element `srcset` generation & handling in templates
- Compatible with both PSR-7 & Symfony HTTP Foundation

Installation, Configuration, & Usage
------------------------------------

See the [Installation](/docs/installation.md), [Configuration](/docs/configuration.md) and [Usage](/docs/usage.md)
files in the `docs/` directory for full documentation.

Standalone Quick Start
----------------------

Create a YAML configuration file:

```yaml
# config/packages/camelot_arbitration.yaml
camelot_arbitration:
    defaults:
        format: webp        
    renditions:
        16x9_1920w:
            ratio: 16x9
            width: 1920
        4x3_1024w:
            ratio: 4x3
            width: 1024

    sets:
        page:
            renditions:
                - 16x9_1920w
                - 4x3_1024w
            media_queries:
                - '(min-width: 1400px) 1024px'
                - '1920px'
```

In your rendered image route controller:

```php
use Camelot\Arbitration\Api\Intervene;
use Camelot\Arbitration\Configuration\Renditions;
use Camelot\Arbitration\DependencyInjection\Configuration;
use Camelot\Arbitration\Filesystem\Filesystem;
use Camelot\Arbitration\Filesystem\Finder;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Manipulators\ManipulatorsFactory;
use Camelot\Arbitration\Responder\FilesystemResponder;
use Camelot\Arbitration\ResponseFactory\SymfonyResponseFactory;
use Intervention\Image\ImageManager;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class RenderedImageController
{
    public function __invoke(string $renderFilePathname): Response
    {
        // Build configuration array
        $config = (new Processor())->processConfiguration(new Configuration(), [Yaml::parseFile(__DIR__ . '/config/packages/camelot_arbitration.yaml')]);

        $renditions = new Renditions($config['renditions'], $config['sets']);
        $imagesFilesystem = new Filesystem($config['image_path']);
        $renderFilesystem = new Filesystem($config['render_path']);
        $pathnameGenerator = new PathnameGenerator();
        $finder = new Finder($imagesFilesystem, $renderFilesystem, $pathnameGenerator);

        $intervene = new Intervene(new ImageManager($config['driver']), ManipulatorsFactory::create());
        $responder = new FilesystemResponder($intervene, $renditions, $renderFilesystem, $pathnameGenerator);
        $responseFactory = new SymfonyResponseFactory(31536000); // 31536000 === 1 year in seconds

        // Determine source & render file information
        $render = $imagesFilesystem->getFileInfo($renderFilePathname);
        $source = $finder->getSourceFromRender($render);

        // Render manipulated image (and cache) if the file does not exist, or has a modification time different to the source
        $renderedImage = $responder->respond($source, $finder->getRenditionNameFromRender($render));

        // Return a response object
        return $responseFactory->create($renderedImage);
    }
}
```
