# ABOUT SASS

Sass is a language that is just normal CSS plus some extra features, like
variables, nested rules, math, mixins, etc. If your stylesheets are written in
Sass, helper applications can convert them to standard CSS so that you can
include the CSS in the normal ways with your theme.

To learn more about Sass, visit: http://sass-lang.com


# INSTALLING `node-sass`

`node-sass` allows you to natively compile `sass` or `scss` files to `css`. You can install the `node-sass`
globaly so you can use it as command line tool.

```bash
npm install -g node-sass
```


# USING `os_style_override` MODULE

Directory structure for the `Harvard school`, `hwpi_modern` and `hwpi_vibrant` theme:

```
os_style_override
|-- README.txt
|-- os_style_override.info
|-- os_style_override.module
|-- sass
    |-- _colors.sass
    |-- harvard-hwpi_modern.sass
    |-- harvard-hwpi_vibrant.sass
```

The `_colors.sass` contains color variables and it needs to be imported
at the top of the theme specific SASS files as follows:

```css
@import "colors"
```

The best approach is to compile SASS files on local or development environment.

## OPENSCHOLAR BUILD PROCESS

Following steps will help you compile SASS files to CSS.

```bash
cd os_style_override
node-sass sass/ -o css/
```

Now, you just need to run the openscholar build script.

## SITE THAT IS LIVE

Following steps will help you compile SASS files to CSS.

```bash
cd os_style_override
node-sass sass/ -o css/
cd ..
tar -zcvf os_style_override.tar.gz os_style_override
```

Now you can upload it to the production server and extract it inside the related school module directory (e.g. harvard).

```bash
cd [OPEN_SCHOLAR_ROOT]/profiles/openscholar/modules/[SCHOOL_DIRECTORY]/
tar -zxvf os_style_override.tar.gz
```

Now you should have a `os_style_override` directory in the related school module. For safety reasons, you can remove the `os_style_override.tar.gz` file or move it to backup directory.


