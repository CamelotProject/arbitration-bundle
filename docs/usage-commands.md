Command Line Tools
===================

Arbitration provides several command line tools (requires `symfony/console`)

 - `arbitration:expire`
 - `arbitration:prime`
 - `arbitration:verify`

Expire Command
--------------

### Description

Expire rendered images for a source image, rendition, or rendition set.

### Usage

```shell
arbitration:expire [options] [--] <type> <name>
```

### Arguments

`type`              Either "file", "rendition", or "set"
`name`              The name of the file, rendition, or set

### Options

`--replace`         Re-render files. Only valid for "file" actions

Prime Command
-------------

### Description

Prime rendered images for a source image, rendition, or rendition set.

### Usage

```shell
arbitration:prime <type> <name> <path>...
```

### Arguments

`type`              Either "rendition" or "set"
`name`              The name of the rendition or set
`path`              Path names to process

Verify Command
--------------

### Description

Verify rendered images against a source image and replace if required.

### Usage

```shell
arbitration:verify [options] [--] [<path>]
```

### Arguments

`path`              Directory path name to verify files in.

### Options

`-r`, `--remove`    Remove orphaned render files, i.e. those without a matching source file

