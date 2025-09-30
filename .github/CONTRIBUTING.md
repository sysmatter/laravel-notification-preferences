# Contributing to [Package Name]

Thanks for considering contributing to [Package Name]! This document outlines the process for contributing to this
project.

## Code of Conduct

This project adheres to a [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include
as many details as possible using the bug report template.

**Good bug reports include:**

- A clear, descriptive title
- Exact steps to reproduce the problem
- Expected vs actual behavior
- Package, Laravel, and PHP versions
- Any relevant logs or error messages

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, use the feature request
template and include:

- A clear description of the problem you're trying to solve
- Your proposed solution
- Any alternative solutions you've considered
- Examples of how the feature would be used

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Install dependencies**: `composer install`
3. **Make your changes** following our coding standards
4. **Add tests** for any new functionality
5. **Run the test suite**: `composer test`
6. **Update documentation** if you're changing functionality
7. **Commit your changes** using clear, descriptive commit messages
8. **Push to your fork** and submit a pull request

## Development Setup

Clone your fork:

```bash
git clone https://github.com/sysmatter/repository-name.git
```

Install dependencies:

```bash
composer install
```

Run tests:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Run code style checks:

```bash
composer format
```

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Write clear, descriptive variable and method names
- Add docblocks for classes and public methods
- Keep methods focused and concise
- Write self-documenting code when possible

### Code Style

We use PHP CS Fixer to maintain consistent code style. Run it before committing:

```bash
composer format
```

### Static Analysis

We use PHPStan/Larastan for static analysis:

```bash
composer analyse
```

## Testing

- Write tests for all new features and bug fixes
- Use Pest for testing
- Aim for high test coverage
- Tests should be clear and describe what they're testing
- Use descriptive test names

```php
it('can process a valid input', function () {
    // Test implementation
});

it('throws an exception for invalid input', function () {
    // Test implementation
})->throws(ValidationException::class);
```

### Running Tests

Run all tests:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

Run specific test file:

```bash
./vendor/bin/pest tests/Unit/SpecificTest.php
```

## Documentation

- Update the README.md if you change functionality
- Add docblocks to all public methods
- Include code examples for new features
- Keep documentation clear and concise

## Commit Messages

Write clear, descriptive commit messages:

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests after the first line

**Examples:**

```
Add support for custom validation rules

Fixes #123
```

```
Refactor query builder for better performance

- Optimize database queries
- Add query caching
- Update tests
```

## Pull Request Process

1. **Ensure tests pass** and code follows our standards
2. **Update documentation** for any changed functionality
3. **Fill out the PR template** completely
4. **Link related issues** using "Fixes #123" or "Closes #456"
5. **Wait for review** - maintainers will review your PR as soon as possible
6. **Address feedback** if changes are requested
7. **Squash commits** if requested before merging

### PR Review Criteria

Maintainers will review PRs based on:

- Code quality and adherence to standards
- Test coverage and quality
- Documentation completeness
- Backwards compatibility
- Performance impact
- Security implications

## Versioning

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality
- **PATCH** version for backwards-compatible bug fixes

## Questions?

- Open a [Discussion](../../discussions) for general questions
- Check existing [Issues](../../issues) for known problems
- Read the [Documentation](link-to-docs)

## License

By contributing, you agree that your contributions will be licensed under the same license as the project.

---

Thank you for contributing! 🎉