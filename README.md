# doctrine-dataloader-sample

Doctrine ORM with a GraphQL-Inspired DataLoader Pattern

See: https://zenn.dev/kalibora/articles/e066d48406bec2

## Usage

```
composer install
```

```
./bin/console doctrine:schema:create
./bin/console doctrine:fixtures:load
```

### Fetch all line items

```
./bin/console app:order:list:all # This triggers an N+1 query pattern.
./bin/console app:order:list:all --eager
```

### Fetch only the highest-subtotal line item

#### Using Criteria

```
./bin/console app:order:list:subtotal-criteria # This triggers an N+1 query pattern.
./bin/console app:order:list:subtotal-criteria --eager
```

#### Using DataLoader Resolver

```
./bin/console app:order:list:subtotal-resolver
```

### Fetch only line items that include whisky

Only the DataLoader Resolver case is provided for this.

```
./bin/console app:order:list:whisky
```
