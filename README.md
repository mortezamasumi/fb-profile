# FB Profile — Edit Profile for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortezamasumi/fb-profile.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-profile)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mortezamasumi/fb-profile/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/mortezamasumi/fb-profile/actions?query=branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mortezamasumi/fb-profile.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-profile)
[![License](https://img.shields.io/packagist/l/mortezamasumi/fb-profile.svg?style=flat-square)](LICENSE.md)

A Filament panel plugin that adds a configurable profile form to the user menu, with Persian-aware inputs (avatar upload, name, NID, gender, birth date, mobile, email, username) and an Iranian national ID validator.

---

## Features

- **Profile page** — replaces the default Filament profile page with a richer, configurable form
- **Avatar upload** — square avatar with configurable disk, folder, visibility and size limit
- **Iranian NID validation** — checksum-validated, with a passport-number bypass option
- **Persian input helpers** — Persian-to-English digit conversion on NID, mobile and email
- **Required-field toggles** — make mobile, email, username, NID, gender or birth date required via config
- **Custom components hook** — extend the form from your user model
- **Localized** — ships English and Persian translations

---

## Installation

```bash
composer require mortezamasumi/fb-profile
```

Add the plugin to your Filament panel provider:

```php
use Mortezamasumi\FbProfile\FbProfilePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FbProfilePlugin::make(),
        ]);
}
```

---

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="fb-profile-config"
```

| Key | Default | Description |
| --- | --- | --- |
| `max_avatar_size` | `200` | Max avatar upload size in KB |
| `avatar_disk` | `public` | Disk for avatar uploads |
| `avatar_visibility` | `public` | Avatar visibility |
| `avatar_folder` | `/uploads/avatars` | Avatar upload folder |
| `mobile_required` | `false` | Require the mobile field |
| `email_required` | `false` | Require the email field |
| `username_required` | `false` | Require the username field |
| `nid_required` | `false` | Require the national ID field |
| `use_passport_number_on_nid` | `false` | Accept passport numbers instead of NIDs |
| `gender_required` | `false` | Require the gender field |
| `birth_date_required` | `false` | Require the birth date field |
| `profile_form_columns` | `3` | Columns in the profile form grid |

---

## Usage

Once the plugin is registered, the profile page is available from the user menu. The form reads your user model's columns; the fields are fully optional unless you enable the `*_required` flags above.

### Extending the form

Your user model can add extra fields or replace the whole form:

```php
// In your User model — prepend the default fields, then append yours
public static function extraProfileComponents(): array
{
    return [
        \Filament\Forms\Components\TextInput::make('bio')->label('Bio'),
    ];
}

// Or define a full custom form
public static function customProfileForm(): array
{
    return [
        \Filament\Forms\Components\TextInput::make('first_name'),
    ];
}
```

### Iranian NID validation

The `iran_nid` validation rule validates the Iranian national ID checksum. It is active in production only, and is bypassed when `use_passport_number_on_nid` is enabled.

```php
$this->validate(['nid' => ['required', 'iran_nid']]);
```

---

## Support policy

| PHP | Laravel |
| --- | --- |
| 8.3 | 12 |

---

## Testing

```bash
composer test
```

The test suite covers the user menu entry, profile updates, redirect behaviour, and the Iranian NID validator using an in-memory SQLite database.

---

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please review our [security policy](.github/SECURITY.md) on how to report it.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

---

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
