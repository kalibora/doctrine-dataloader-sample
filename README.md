# doctrine-dataloader-sample

Implementing the GraphQL‑Inspired DataLoader Pattern in Doctrine ORM

## Usage

```
composer install
```

```
./bin/console doctrine:schema:create
./bin/console doctrine:fixtures:load
```

### 全明細を取得

```
./bin/console app:order:list:all # This triggers an N+1 query pattern.
./bin/console app:order:list:all --eager
```

### 小計の一番高い明細のみを取得

#### Criteria を用いるケース

```
./bin/console app:order:list:subtotal-criteria # This triggers an N+1 query pattern.
./bin/console app:order:list:subtotal-criteria --eager
```

#### DataLoader Resolver を用いるケース

```
./bin/console app:order:list:subtotal-resolver
```

### Whiskyを含む明細のみを取得

これは DataLoader Resolver を用いるケースのみ用意

```
./bin/console app:order:list:whisky
```
