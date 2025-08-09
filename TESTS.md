# CssMaker Test Suite Documentation

## Overview
Comprehensive unit test suite for the CssMaker library covering all core functionalities with proper separation of concerns and modular test organization.

## Test Architecture

### Test Structure Organization

The test suite is organized into focused test classes, each covering specific aspects of the CssMaker functionality:

#### Core CssMaker Tests

1. **CssMakerTest** - Basic functionality and configuration
   - Constructor with optional Output parameter
   - Path setters and build path configuration
   - Directory validation and exception handling
   - Fluent interface validation

2. **CssMakerAddLessTest** - LESS file management
   - Adding LESS files with filtering
   - File type categorization
   - Underscore file exclusion (files starting with `_`)
   - Non-existent file handling
   - Empty array processing

3. **CssMakerToCssTest** - LESS to CSS conversion
   - LESS compilation using `lessc` command
   - File existence validation
   - Invalid LESS syntax handling
   - External dependency management

4. **CssMakerPostcssTest** - PostCSS processing
   - Autoprefixer integration
   - CSS file validation
   - Invalid CSS handling
   - PostCSS configuration management

5. **CssMakerMinifyTest** - CSS minification
   - CSS minification using `minify` command
   - File size verification
   - Missing file handling
   - Empty file processing

6. **CssMakerAddFontTest** - Font management
   - Font object validation
   - Font file copying
   - Duplicate font ID handling
   - Missing font file scenarios

#### Supporting Component Tests

7. **VariablesTest** - LESS variables management
   - YAML file loading and parsing
   - Variable export to LESS format
   - Error handling for invalid files
   - Special character and encoding support

8. **MergerTest** - File and content merging
   - Variable, mixin, and file merging
   - Content generation and ordering
   - File handling (existing/non-existing)
   - String content additions

9. **CleanerTest** - CSS cleaning and optimization
   - CSS comment removal
   - Whitespace normalization
   - Line ending standardization
   - Complex CSS structure handling

10. **LessMakerExceptionTest** - Exception handling
    - Custom exception creation
    - Exception message handling
    - Exception chaining and inheritance

#### Integration Tests

11. **IntegrationTest** - End-to-end workflow testing
    - Complete CSS build process
    - Real-world usage scenarios
    - Error handling in complex workflows
    - Multi-file type processing

## Test Infrastructure

### Helper Classes

- **Helper.php** - Utility functions for test setup and cleanup
  - Temporary directory creation and management
  - File system operations
  - Test data generation

- **InitializedMakerCase.php** - Base test case with pre-configured CssMaker instance
  - Common test setup and teardown
  - Shared fixtures and directory structure
  - Consistent test environment

### Test Fixtures

Organized fixture files for realistic testing scenarios:

#### LESS Fixtures (`tests/fixtures/less/`)
- `valid1.less`, `valid2.less` - Valid LESS files for testing
- `_underscore.less` - Underscore-prefixed file (should be ignored)
- `mixins.less` - LESS mixins for testing
- `normalize.less` - CSS normalization rules
- `structure.less` - Basic structure styles
- `variables.yml` - LESS variables in YAML format
- `invalid.less` - Invalid LESS syntax for error testing
- `empty.less` - Empty file for edge case testing

#### CSS Fixtures (`tests/fixtures/css/`)
- `valid.css` - Valid CSS for PostCSS testing
- `invalid.css` - Invalid CSS for error testing
- `empty.css` - Empty CSS file for edge cases

#### PostCSS Fixtures
- `postcss.test.css` - CSS content for autoprefixer testing
- `maker.test.less` - LESS content for compilation testing

## Test Features

### Cross-Platform Compatibility
- All tests work on Windows, Linux, and macOS
- Proper directory separator handling
- Path normalization for different filesystems

### External Dependency Management
- Graceful handling of missing external tools (`lessc`, `postcss`, `minify`)
- Test skipping when dependencies are unavailable
- Proper error messages for missing tools

### File System Testing
- Comprehensive temporary file management
- Automatic cleanup of test artifacts
- Safe handling of file operations
- Permission and access testing

### Error Handling Validation
- Exception type and message verification
- Edge case scenario testing
- Invalid input handling
- Resource availability checking

### Method Testing Patterns
- **Reflection-based testing** for protected methods
- **Fluent interface validation** for method chaining
- **State verification** using property inspection
- **External process mocking** for command-line tools

## Test Execution

### Running Tests

```bash
# Run all tests
composer test

# Run specific test class
vendor\bin\phpunit tests\CssMakerTest.php

# Run tests and stop on first failure
vendor\bin\phpunit --stop-on-failure
```

### Test Configuration

- **PHPUnit 10.5+** - Modern PHPUnit features and attributes
- **PHP 8.4+** - Latest PHP features and compatibility
- **PSR-4 autoloading** - Proper namespace organization
- **Bootstrap setup** - Consistent test environment initialization

## Test Results Summary

- ✅ **Comprehensive test coverage** across all major components
- ✅ **Modular test organization** with focused test classes
- ✅ **Robust error handling** validation
- ✅ **Cross-platform compatibility** verified
- ✅ **External dependency management** implemented
- ✅ **Clean separation of concerns** in test architecture

## Continuous Integration

The test suite is designed to work in CI environments:
- **Dependency isolation** - Tests skip gracefully when external tools are missing
- **No hard dependencies** on system-installed tools
- **Consistent behavior** across different environments
- **Proper exit codes** for automated testing

## Development Workflow

### Adding New Tests

1. Create focused test classes for specific functionality
2. Use appropriate base classes (`TestCase` or `InitializedMakerCase`)
3. Follow existing naming conventions
4. Add fixtures to appropriate directories
5. Include both positive and negative test cases
6. Test error conditions and edge cases

### Test Maintenance

- Keep fixtures up to date with code changes
- Update test documentation when adding new test classes
- Ensure cross-platform compatibility for new tests
- Validate external dependency handling
