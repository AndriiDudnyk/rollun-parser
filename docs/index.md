
# rollun-parser

Сервис предоставляет возможность загружать и парсить страницы.

## Webhooks
* `/api/webhook/ebay-loaders` - запустить один раз загрузчик страниц для ebay
* `/api/webhook/proxy-loaders` - запустить один раз загрузчик страниц для proxy-free-list
* `/api/webhook/ebay-parsers` - запустить один раз парсинг страниц для ebay
* `/api/webhook/proxy-parsers` - запустить один раз загрузчик страниц для proxy-free-list

## DataStores
* `ebay-product-page-store` - загрузить данные со страницы продукта
* `ebay-simple-search-page-store` - загрузить данные со страницы поиска типа `https://www.ebay.com/sch/eBay-Motors`
* `ebay-motors-search-page-store` - загрузить данные со страницы поиска типа `https://www.ebay.com/sch/`
* `ebay-compatible-page-store` - загрузить данные с ebay API типа `https://frame.ebay.com/ebaymotors/ws/eBayISAPI.dll?GetFitmentData`




