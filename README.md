# X-Ray your TYPO3 installation

This extension is a collection of utility commands that scan a TYPO3 installation for
potential integrity improvements.

## External links that could be internal Links

The Command

```
./bin/typo3 xray:external-links --dry-run
```

lists all external links that could be converted to internal links. This supports links to pages and files.

Without the `--dry-run` option the migration will be performed and the links will be rewritten in the `t3://` syntax.

## Sharing our expertise

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
