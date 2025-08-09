# CssMaker

A modern PHP library for building optimized CSS files from LESS sources with advanced preprocessing, autoprefixing, and minification capabilities.

## Features

- 🎨 **LESS Compilation**: Process LESS files with variables, mixins, and nesting
- 🚀 **PostCSS Integration**: Automatic vendor prefix addition and CSS optimization
- 🧹 **CSS Optimization**: Comment removal, whitespace cleaning, and minification
- 📁 **Modular Organization**: Structured approach with categorized file types
- 🔧 **Font Management**: Built-in font loading and @font-face generation
- ⚡ **Performance**: Efficient temporary file handling and process pipeline
- 🧪 **Fully Tested**: Comprehensive test suite with modular test architecture
- 🔄 **Fluent Interface**: Method chaining for clean, readable code

## Installation

```bash
composer require jdz/cssmaker
```

### Node.js Dependencies

CssMaker requires Node.js tools for CSS processing. Install them automatically:

```bash
composer npm:local
composer npm:global
```

## Dependencies

jdz/fontmanager is required for font management and @font-face generation.
jdz/data is required for data handling and processing.
jdz/output is required for output handling and logging.

## Quick Start

```php
<?php

use JDZ\CssMaker\CssMaker;

// Initialize with optional custom output handler
$cssMaker = new CssMaker();

// Set build paths (creates directory structure automatically)
$cssMaker->setBuildPaths('/path/to/project', 'build');

// Add LESS files by category
$cssMaker->addLessFiles([
    'variables' => ['config/variables.yml'],
    'mixins' => ['src/mixins.less'],
    'normalize' => ['src/normalize.less'],
    'structure' => ['src/layout.less', 'src/components.less'],
    'mobile' => ['src/mobile.less'],
    'print' => ['src/print.less']
]);

// Add fonts (optional)
// see jdz/fontmanager for more infos
$fontData = (object) [
    'id' => 'roboto',
    'family' => 'Roboto',
    'display' => 'swap',
    'style' => 'normal',
    'weight' => '400',
    'files' => ['fonts/roboto.woff2', 'fonts/roboto.woff']
];
$cssMaker->addFont($fontData);

// Process and build CSS (creates: theme.less, theme.css, theme.min.css)
$cssMaker->process('theme');
```

## File Categories

CssMaker organizes LESS files into logical categories processed in order.
You can extend the CssMaker class and add your own categories as needed.

### Core Categories
- **`variables`**: LESS/YAML variables and configuration
- **`mixins`**: Reusable LESS mixins and functions  
- **`normalize`**: CSS reset and normalization rules
- **`animations`**: CSS animations and keyframes
- **`fonts`**: Font declarations and @font-face rules

### Layout Categories
- **`structure`**: Main layout and component styles
- **`icons`**: Icon fonts and SVG styles

### Responsive Categories  
- **`mobile`**: Mobile-first styles (wrapped in `@media(max-width: @screen-breakpoint - 1px)`)
- **`screen`**: Desktop styles (wrapped in `@media(min-width: @screen-breakpoint)`)
- **`queries`**: Custom media queries
- **`print`**: Print-specific styles (wrapped in `@media print`)

## Configuration Files

### PostCSS Configuration (`postcss.json`)

```json
{
    "use": [
        "autoprefixer",
        "postcss-discard-comments",
        "postcss-safe-parser"
    ],
    "remove": true,
    "add": true,
    "autoprefixer": {
        "browsers": "> .5%, last 2 versions, not dead"
    }
}
```

### Browserslist Configuration (`.browserslistrc`)

```
> .5%
last 2 versions
not dead
```

## Advanced Features

### Variable Management

```php
use JDZ\CssMaker\Variables;

// Variables support YAML files
$variables = new Variables();
$variables->set('primary-color', '#3498db');
$variables->set('screen-breakpoint', '768px');

// YAML format (variables.yml):
# primary_color: "#3498db"
# screen_breakpoint: "768px"
# font_sizes:
#   small: "12px"
#   medium: "16px"
#   large: "24px"
```

### Custom Output Handler

```php
use JDZ\Output\Output;

$output = new Output();
$output->setVerbosity(Output::VERBOSITY_ALL);

$cssMaker = new CssMaker($output);
// Now you'll see detailed processing information
```

## Processing Pipeline

The CssMaker follows a structured build pipeline:

1. **🔧 Preparation**
   - Load variables from YAML/LESS files
   - Validate directory structure
   - Initialize temporary file management

2. **📝 Content Merging** 
   - Merge variables into LESS format
   - Combine mixins and normalize files
   - Add font @font-face declarations
   - Process files by category with media query wrapping

3. **⚙️ LESS Compilation**
   - Compile merged LESS to CSS using `lessc`
   - Handle variables, mixins, nesting, and functions
   - Generate source CSS file

4. **🎯 PostCSS Processing**
   - Apply autoprefixer for vendor prefixes
   - Process with PostCSS plugins
   - Use browserslist configuration

5. **🧹 Optimization**
   - Remove CSS comments and unnecessary whitespace
   - Clean and normalize formatting
   - Apply minification

6. **📦 Output Generation**
   - Generate final CSS file
   - Create minified version (.min.css)
   - Clean up temporary files

## Directory Structure

```
project/
└── build/                 # Output directory
    ├── css/               # Generated CSS files
    │   ├── theme.less       # Merged LESS
    │   ├── theme.css        # Compiled CSS
    │   └── theme.min.css    # Minified CSS
    └── fonts/             # Copied font files
    └── images/            # Copied image files
├── fonts/                 # Font source files (optional, used by the font manager)
├── less/                  # LESS source files
│   ├── variables.yml        # LESS variables
│   ├── mixins.less          # LESS mixins
│   ├── normalize.less       # CSS normalization
│   ├── structure.less       # Main styles
│   ├── mobile.less          # Mobile styles
│   └── print.less           # Print styles
├── tmp/                   # Temporary files (auto-created)

```

## Error Handling

CssMaker provides comprehensive error handling:

```php
use JDZ\CssMaker\Exception\LessMakerException;

try {
    $cssMaker->process('theme');
} catch (LessMakerException $e) {
    // Handle CssMaker-specific errors
    echo "CSS Build Error: " . $e->getMessage();
} catch (\Exception $e) {
    // Handle general errors
    echo "General Error: " . $e->getMessage();
}
```

**Common Error Scenarios:**
- Missing required directories
- Invalid LESS syntax
- Missing Node.js dependencies (`lessc`, `postcss`, `minify`)
- File permission issues
- Malformed configuration files

## Testing

CssMaker includes a comprehensive test suite. See [TESTS.md](TESTS.md) for detailed information.

```bash
# Run all tests
composer test

# Run specific test classes
composer test:cssmaker
composer test:cleaner  
composer test:variables
composer test:merger
```

## Example Usage

See the complete working example:

```bash
composer example
```

The example demonstrates:
- Full project setup with directory structure
- Extended CssMaker class
- Configuration file usage
- Font integration
- Multiple file type processing
- Error handling

## Browser Support

Default configuration targets:

- **Modern Browsers**: Chrome, Firefox, Safari, Edge (last 2 versions)
- **Market Share**: Browsers with > 0.5% usage
- **Security**: Excludes browsers without security updates
- **Mobile**: iOS Safari, Chrome Mobile

Customize in `.browserslistrc`:

```
# Conservative approach
> 1%
last 3 versions
not dead

# Modern approach  
> 0.25%
last 2 versions
not dead
not ie 11

# Legacy support
> 0.5%
last 2 versions
not dead
ie >= 11
```

## Requirements

- **PHP**: >= 8.1
- **Composer**: For dependency management
- **Node.js**: >= 14.0 (for LESS, PostCSS, and minification tools)

### PHP Dependencies
- `jdz/output`: ^1.0 (Output handling)
- `jdz/data`: ^1.0 (Data processing)
- `symfony/yaml`: ^7.2 (YAML parsing)
- `symfony/process`: ^7.2 (External process execution)
- `jdz/fontmanager`: ^1.0 (Font management - optional)

## Performance Tips

1. **Use file caching** - Only rebuild when source files change
2. **Minimize file count** - Combine related LESS files
3. **Optimize images** - Use appropriate formats and compression
4. **Monitor build time** - Profile with verbose output
5. **Cache font files** - Reuse font declarations across builds

## Troubleshooting

### Common Issues

**"lessc command not found"**
```bash
npm install -g less
```

**"postcss command not found"**  
```bash
npm install -g postcss postcss-cli autoprefixer postcss-safe-parser postcss-discard-comments
```

**"minify command not found"**
```bash
npm install -g minify
```

**"Permission denied" errors**
```bash
chmod 755 /path/to/build/directory
```

**"LESS compilation failed"**
- Check LESS syntax in source files
- Verify variable names and references
- Ensure all imported files exist

### Debug Mode

Enable verbose output for troubleshooting:

```php
use JDZ\Output\Output;

$output = new Output();
$output->setVerbosity(Output::VERBOSITY_ALL);

$cssMaker = new CssMaker($output);
// Detailed processing information will be displayed
```

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Add tests for new functionality
4. Ensure all tests pass (`composer test`)
5. Follow PSR-12 coding standards
6. Update documentation as needed
7. Commit changes (`git commit -m 'Add amazing feature'`)
8. Push to branch (`git push origin feature/amazing-feature`)
9. Open a Pull Request

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

## Support

- 📧 **Email**: joffrey.demetz@gmail.com
- 🌐 **Website**: https://joffreydemetz.com
- 📦 **Package**: https://packagist.org/packages/jdz/cssmaker
- 🐛 **Issues**: [GitHub Issues](https://github.com/joffreydemetz/cssmaker/issues)

