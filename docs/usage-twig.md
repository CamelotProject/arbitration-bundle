Twig
====

Arbitration's Twig extension provides the `rendition` and `srcset` functions. 

`rendition`
-----------

Determine the render path for a single rendition, e.g.:

```twig
<img src="{{ rendition('/uri/image.jpg', '16x9_730w') }}">
```

Would output something similar to:

```html
<img src="/render/16x9_730w/jpg/images/image.webp">
```

`srcset`
--------

Calculate both `srcset` and `sizes` attributes for a give source image based upon the configured
rendition set, e.g.:

```twig
<img src='{{ '/uri/image.jpg' }}' {{ srcset('/uri/image.jpg', 'srcset_name') }}>
```

Would output something similar to:

```html
<img src="/images/image.jpg"
     srcset="/render/16x9_270w/jpg/images/image.webp 270w,
             /render/16x9_370w/jpg/images/image.webp 370w,
             /render/16x9_730w/jpg/images/image.webp 730w" 
     sizes="(min-width: 1024px) 730px,
            (min-width: 768px) 370px,
            270px" 
>
```
