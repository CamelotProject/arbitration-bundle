Configuration
=============

Webserver Configuration
-----------------------

See the page on [webserver configuration](./configuration-webserver.md) for configuring your webserver to serve the 
static images, and fallback to PHP when the rendered image version is not found. 

YAML Configuration File
------------------------

Configuration of the library is done via a YAML file. 

In Symfony based projects that file can be found at`config/packages/camelot_arbitration.yaml`, but there is no hard
requirement on its location for standalone installations.

Below is a full extract of available configuration options.

```yaml
camelot_arbitration:
    driver: gd # One of "gd"; "imagick"
    image_path: '%kernel.project_dir%/public'
    render_path: '%kernel.project_dir%/public/render'
    responder: Camelot\Arbitration\Responder\Responder
    defaults:
        # Default rendition settings.
    renditions:
        # Prototype
        name:
            # Device pixel ratio.
            dpr: 1.0
            # Encodes the image to a specific format. Accepts 'jpg', 'pjpg' (progressive jpeg), 'png', 'gif', 'webp' or 'avif'. Defaults to 'jpg'.
            format: ~
            # Defines the quality of the image. Use values between 0 and 100. Defaults to 90. Only relevant if the format is set to 'jpg' or 'pjpg'.
            quality: ~
            # Limit the maximum pixels size (count) of generated images, i.e. width multiplied by height.
            max_size: ~
            # The ratio of these image's width to height. e.g.: '4:3', '4x3', '1.333'.
            ratio: ~
            # Sets the width of the image, in pixels.
            width: ~
            # Sets the height of the image, in pixels.
            height: ~
            # Rotates the image. Accepts 'auto', 0, 90, 180 or 270. Default is 'auto'. The auto option uses Exif data to automatically orient images correctly.
            orientation: ~ # One of null; "auto"; 0; 90; 180; 270
            # Crops the image to specific dimensions prior to any other resize operations. Required format: width,height,x,y
            crop: ~
            # Sets how the image is fitted to its target dimensions. Accepts:
            #  - contain: (default) Resizes the image to fit within the width and height boundaries without cropping, distorting or
            #             altering the aspect ratio.
            #  - max:     Resizes the image to fit within the width and height boundaries without cropping, distorting or altering
            #             the aspect ratio, and will also not increase the size of the image if it is smaller than the output size.
            #  - fill:    Resizes the image to fit within the width and height boundaries without cropping or distorting the image,
            #             and the remaining space is filled with the background color. The resulting image will match the
            #             constraining dimensions.
            #  - stretch: Stretches the image to fit the constraining dimensions exactly. The resulting image will fill the
            #             dimensions, and will not maintain the aspect ratio of the input image.
            #  - crop:    Resizes the image to fill the width and height boundaries and crops any excess image data. The resulting
            #             image will match the width and height constraints without distorting the image.
            fit: ~ # One of null; "contain"; "max"; "fill"; "stretch"; "crop"
            # Flips the image. Accepts 'v', 'h' and 'both'.
            flip: ~ # One of null; "v"; "h"; "both"
            # Sets the background color of the image. Accepts hexadecimal RGB and RBG alpha formats.
            background: ~
            # Adjusts the image brightness. Use values between -100 and +100, where 0 represents no change.
            brightness: ~
            # Adjusts the image contrast. Use values between -100 and +100, where 0 represents no change.
            contrast: ~
            # Adjusts the image gamma. Use values between 0.1 and 9.99.
            gamma: ~
            # Adds a blur effect to the image. Use values between 0 and 100.
            blur: ~
            # Applies a filter effect to the image. Accepts 'greyscale' or 'sepia'.
            filter: ~ # One of null; "greyscale"; "sepia"
            # Applies a pixelation effect to the image. Use values between 0 and 1000.
            pixelate: ~
            # Sharpen the image. Use values between 0 and 100.
            sharpen: ~
            # Adds a watermark to the image.
            watermark:
                # Path to an image to be used as the watermark
                path: ~ # Required
                # Sets the width of the watermark in pixels, or using relative dimensions.
                width: ~
                # Sets the height of the watermark in pixels, or using relative dimensions.
                height: ~
                # Sets how the watermark is fitted to its target dimensions. Accepts:
                #  - contain: (default) Resizes the image to fit within the width and height boundaries without cropping, distorting or
                #             altering the aspect ratio.
                #  - max:     Resizes the image to fit within the width and height boundaries without cropping, distorting or altering
                #             the aspect ratio, and will also not increase the size of the image if it is smaller than the output size.
                #  - fill:    Resizes the image to fit within the width and height boundaries without cropping or distorting the image,
                #             and the remaining space is filled with the background color. The resulting image will match the
                #             constraining dimensions.
                #  - stretch: Stretches the image to fit the constraining dimensions exactly. The resulting image will fill the
                #             dimensions, and will not maintain the aspect ratio of the input image.
                #  - crop:    Resizes the image to fill the width and height boundaries and crops any excess image data. The resulting
                #             image will match the width and height constraints without distorting the image.
                fit: ~ # One of null; "contain"; "max"; "fill"; "stretch"; "crop"
                # Sets how far the watermark is away from the left and right edges of the image. Set in pixels, or using relative dimensions. Ignored if 'position' is set to 'center'.
                offset_x: ~
                # Sets how far the watermark is away from the top and bottom edges of the image. Set in pixels, or using relative dimensions. Ignored if 'position' is set to 'center'.
                offset_y: ~
                # Sets how far the watermark is away from edges of the image. Basically a shortcut for using both 'offset_x' and 'offset_x'. Set in pixels, or using relative dimensions. Ignored if 'position' is set to 'center'.
                padding: ~
                # Sets where the watermark is positioned. Accepts 'top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'. Default is 'center'.
                position: ~
                # Sets the opacity of the watermark. Use values between 0 and 100, where 100 is fully opaque, and 0 is fully transparent.
                alpha: ~
    sets:
        # Prototype
        name:
            # List of rendition names to assign to the set
            renditions: [ ]
            # List of media queries for the set
            media_queries: [ ]
```

Routing
-------

If you are using dynamic image creation then you will need to define a controller route pointing to an 
appropriate controller for your project.

Arbitration comes with two ready-to-go controllers, `Camelot\Arbitration\Controller\Psr7ImageController` for PSR-7
based projects, and `Camelot\Arbitration\Controller\SymfonyImageController` for Symfony projects. Additionally, there
is also the trait `Camelot\Arbitration\Controller\ControllerTrait` available for the D.I.Y. controller enthusiasts.

### Symfony

```yaml
camelot_arbitration:
    # ...
    responder: Camelot\Arbitration\Responder\FilesystemResponder
    # ...
```

```yaml
# config/routes.yaml

images:
    path: /render/{path}
    controller: Camelot\Arbitration\Controller\SymfonyImageController
    requirements: { path: '.+' }:
```

**Next:** [Usage](./usage.md)

**Previous:** [Installation](./installation.md)
