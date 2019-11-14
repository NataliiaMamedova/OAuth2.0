# Simple OAuth 2.0 library for Symfony 4 projects

At this time, library allow to auth using JWT with OAuth2 `Authorization Code` flow.


# Basic configuration

1. Enable session handler in your application

```yml
framework:
    session:
        handler_id: ~
```

2. Configure DB connection, then add new entity manager `auth` to your application config

```yml
doctrine:
    orm:
        entity_managers:
            auth:
                connection: default ## may be your custom connection
                default_repository_class: 'DpDocument\Auth\Repository\UserRepository'
                mappings:
                    Auth:
                        is_bundle: false
                        type: annotation
                        dir: '%kernel.project_dir%/vendor/DpDocument/auth/src/Entity'
                        prefix: 'DpDocument\Auth\Entity'
                        alias: Auth
```

3. Configure encoder and provider in `security` section

```yml
security:
    encoders:
        DpDocument\Auth\Entity\User:
            algorithm: plaintext
    providers:
        auth_provider:
            id: DpDocument\Auth\Security\UserProvider
```

# Configuration of authentication through OAuth2 service (`authorization_code` flow)

1. Configure your main firewall to use package provider and set login form config like bellow

```yml
security:
    firewalls:
        main:
            ### If you want to use OAuth2 Access Code authentication flow
            provider: auth_provider
            anonymous: ~
            logout: ~
            form_login:
                login_path: auth
                check_path: auth            
```

2. Add access control rules

```yml
access_control:
    - { path: ^/auth, roles: IS_AUTHENTICATED_ANONYMOUSLY }
    - { path: ^/, roles: IS_AUTHENTICATED_FULLY }
```

# Configuration for checking authentication using JSON Web Token

In this case we use `Authorization: Bearer <MY JWT>` header

1. Configure any provider (such like a `memory`)

```yml
security:
    providers:
        in_memory: { memory: ~ }
```

2. Then add to your main firewall this provider and guard authenticator

```yml
security:
    firewalls:
        main:
            provider: in_memory
            guard:
                authenticators:
                    - DpDocument\Auth\Security\Authenticator\JwtAuthenticator
            stateless: true
```

3. Also configure your access rules if needed

```yml
access_control:
    - { path: ^/, roles: IS_AUTHENTICATED_FULLY }
```

### @ DpDocument | Research & Development

