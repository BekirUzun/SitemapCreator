# SitemapCreator
Super simple sitemap creator writen in PHP

### Usage
Create an object from SitemapCreator class and call Create function like that:

```php
$creator = new SitemapCreator();
$sitemap = $creator->Create("https://github.com", 50);
```
In this example we created sitemap of https://github.com with maximum 50 links. Check [examples](/example) for fully working code.
