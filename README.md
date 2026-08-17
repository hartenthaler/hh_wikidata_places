# **webtrees** module: Wikidata Places

![webtrees major version](https://img.shields.io/badge/webtrees-v2.2.x-green)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

Wikidata Places connects [Vesta Shared Places](https://github.com/vesta-webtrees-2-custom-modules/vesta_shared_places) (`_LOC`) in [webtrees](https://www.webtrees.net) with public information from Wikidata. It keeps the genealogical data in webtrees and uses Wikidata only as a read-only external source.

## 📚 Contents

* [Purpose](#purpose)
* [Main features](#main-features)
* [Privacy](#privacy)
* [Requirements](#requirements)
* [Installation](#installation)
* [Documentation](#documentation)
* [Translation](#translation)
* [Credits](#credits)
* [License](#license)

## 🎯 Purpose

A shared place can represent a building, farm, church, cemetery, street, square, district, village or another real-world place. This module stores a Wikidata QID alongside the shared-place record and displays public Wikidata information such as its label, description, type and image.

The module does not copy Wikidata data into GEDCOM, synchronize with Wikidata or send genealogical person data to Wikidata. A later optional link to Domus will open the specialised Domus application rather than duplicating its functions in webtrees.

## ⚙️ Main features

The first release provides the foundation for:

* recognising Wikidata identifiers stored as `_EXID` with the Wikidata entity authority URI;
* validating QIDs and keeping other external identifiers untouched;
* loading and caching public Wikidata entity information;
* displaying read-only Wikidata information through Vesta's place-information provider mechanism;
* using the webtrees language with a sensible fallback; and
* showing Wikidata and Wikimedia Commons provenance for displayed data and images.

Manual search and assignment, nearby discovery, Domus deep links and extended house-history metadata follow in later milestones.

## 🔒 Privacy

When a Wikidata entry is loaded, the server requests only the public Wikidata QID and the requested display language. Standard technical request metadata is sent by the server. The module never sends names of living people, family relationships, private notes, sources or other genealogical data.

Responses are cached locally. If Wikidata is temporarily unavailable, the normal Vesta Shared Place page remains available and a previously cached response may be shown with its cache status.

## 📌 Requirements

* webtrees 2.2.x
* Vesta Shared Places
* PHP with HTTPS access to the Wikidata APIs for live enrichment; cached data remains usable while offline

## 📥 Installation

1. Download the [latest release](https://github.com/hartenthaler/hh_wikidata_places/releases/latest).
2. Unzip it into the `modules_v4` directory of your webtrees installation.
3. Ensure that the directory is named `hh_wikidata_places`.
4. In the webtrees control panel, enable **Wikidata Places**.
5. In the Vesta Shared Places configuration, enable **Wikidata Places** as a place-information provider.

For a linked shared place, use an external identifier with a Wikidata QID and the authority URI `https://www.wikidata.org/entity/`, for example:

```gedcom
1 _EXID Q123456
2 TYPE https://www.wikidata.org/entity/
```

## 📖 Documentation

* [Concept: Wikidata and Domus integration](docs/WIKIDATA_DOMUS_CONCEPT.md)
* [Roadmap](docs/ROADMAP.md)
* [Development notes](docs/DEVELOPMENT.md)
* [Changelog](CHANGELOG.md)

## 🌐 Translation

The module uses the standard webtrees gettext translation system. Translation files are kept in `resources/lang`; contributions are welcome as pull requests.

## 🙏 Credits

* [webtrees](https://www.webtrees.net)
* [Vesta Shared Places](https://github.com/vesta-webtrees-2-custom-modules/vesta_shared_places)
* [Wikidata](https://www.wikidata.org) and [Wikimedia Commons](https://commons.wikimedia.org)

## ⚖️ License

This module is licensed under [GPL-3.0-or-later](LICENSE).
