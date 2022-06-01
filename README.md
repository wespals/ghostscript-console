# Ghostscript Console
A Symfony CLI utility for the Ghostscript executable

### Installation
```sh
composer require wespals/ghostscript-console
```

### Usage
```sh
php vendor/bin/ghostscript-console ghostscript -g 'sDEVICE=pdfwrite' --gs='dCompatibilityLevel=1.4' ./tests/files/2019FormW2-PDFv1.7.pdf ./tests/files/temp/ghostscript-output.pdf
```

### Default parameter switches
#### The following parameters are automatically set if not explicitly included in the command.
* -dSAFER
* -dBATCH
* -dNOPAUSE
* -sDEVICE=pdfwrite

### View command help and examples
```sh
php vendor/bin/ghostscript-console ghostscript --help
```

### How to use Ghostscript
[Documentation](https://www.ghostscript.com/doc/current/Use.htm)
