# Symfony Router wrapper

Allows automatic loading controller files and actions

Usage example `app/config/routing.yml`

```yml
api:
  resource: "@ApiBundle/Controller/"
  type:     automatic
  prefix:   /
```