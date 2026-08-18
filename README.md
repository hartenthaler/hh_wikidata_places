# **webtrees** module: Wikidata Places

![webtrees major version](https://img.shields.io/badge/webtrees-v2.2.x-green)
[![Module version](https://img.shields.io/badge/version-0.1.0-blue)](version.txt)
[![Downloads](https://img.shields.io/github/downloads/hartenthaler/hh_wikidata_places/total?label=downloads)](https://github.com/hartenthaler/hh_wikidata_places/releases)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

Wikidata Places connects [Vesta Shared Places](https://github.com/vesta-webtrees-2-custom-modules/vesta_shared_places) (`_LOC`) in [webtrees](https://www.webtrees.net) with public information from Wikidata. It keeps genealogical data in webtrees and uses Wikidata as a read-only external source.

## 📚 Contents

* [Purpose](#purpose)
* [Main features](#main-features)
* [Domus](#domus)
* [Privacy](#privacy)
* [Requirements](#requirements)
* [Installation](#installation)
* [Documentation](#documentation)
* [Translation](#translation)
* [Credits](#credits)
* [License](#license)

## 🎯 Purpose

A shared place can represent a building, farm, church, cemetery, street, square, district, village or another real-world place. This module stores a Wikidata QID alongside the shared-place record and displays public Wikidata information such as its label, description, type and image.

The module does not synchronize with Wikidata or send genealogical person data to Wikidata. Assigning, replacing or removing a QID is always an explicit action by a user who may edit the shared place. Read-only display never changes GEDCOM data.

## 🏠 Domus

[Domus](https://domus.genealogy.net) is an open application for researching the history of houses and buildings. It complements webtrees: webtrees remains the place for private genealogical data, while Domus can provide specialised public research and map views. This module will offer an optional outbound link to a Domus map for a linked Wikidata QID in a later milestone.

## ⚙️ Main features

The current release provides:

* recognising Wikidata identifiers stored as `_EXID` with the Wikidata entity authority URI;
* validating QIDs and keeping other external identifiers untouched;
* loading and caching public Wikidata entity information;
* displaying read-only Wikidata information through Vesta's place-information provider mechanism;
* using the webtrees language with a sensible fallback; and
* showing Wikidata and Wikimedia Commons provenance for displayed data and images;
* a protected search page for finding, reviewing and manually assigning Wikidata items; and
* controlled removal and replacement of stored QIDs, including an explicit offer to replace a redirected Wikidata item and an updated shared-place change record.

Nearby discovery, Domus deep links and extended house-history metadata follow in later milestones.

## 🔒 Privacy

When a Wikidata entry is loaded, the server requests only the public Wikidata QID and the requested display language. Standard technical request metadata is sent by the server. The module never sends names of living people, family relationships, private notes, sources or other genealogical data.

Responses are cached locally. If Wikidata is temporarily unavailable, the normal Vesta Shared Place page remains available.
When the optional Legal Notice module is active, it includes Wikidata in the generated privacy policy together with the purpose of the request and the transferred technical data.

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

An editor can use **Assign Wikidata item** on the shared-place page to search Wikidata and choose an item. The module stores it as an external identifier with the Wikidata authority URI:

```gedcom
1 _EXID Q123456
2 TYPE https://www.wikidata.org/entity/
```

If an existing item has been merged or redirected by Wikidata, the assignment page shows the replacement QID and lets the editor apply it explicitly.

## 📖 Documentation

* [Concept: Wikidata and Domus integration](docs/WIKIDATA_DOMUS_CONCEPT.md)
* [Roadmap](docs/ROADMAP.md)
* [Development notes](docs/DEVELOPMENT.md)
* [Changelog](CHANGELOG.md)

## 🌐 Translation

The module uses the standard webtrees gettext translation system. Translation files are kept in `resources/lang`; German is currently available. Contributions are welcome as pull requests.

## 🙏 Credits

* [webtrees](https://www.webtrees.net)
* [Vesta Shared Places](https://github.com/vesta-webtrees-2-custom-modules/vesta_shared_places)
* [Wikidata](https://www.wikidata.org) and [Wikimedia Commons](https://commons.wikimedia.org)
* David Straub for developing [Domus](https://domus.genealogy.net), and the Domus community

## ⚖️ License

This module is licensed under [GPL-3.0-or-later](LICENSE).
