Self-hosted webfonts
====================

  bricolage-grotesque-*.woff2   Bricolage Grotesque   display / headings
  instrument-sans-*.woff2       Instrument Sans       body text
  jetbrains-mono-*.woff2        JetBrains Mono        eyebrows, numerals

All three are VARIABLE fonts: a single file per subset covers every weight the
design uses, which is why there are six files rather than eighteen.

Each family ships in two subsets. The browser picks between them using the
unicode-range in assets/css/fonts.css and only downloads latin-ext if a
character actually needs it.

Licence
-------
All three families are licensed under the SIL Open Font License, Version 1.1
(see OFL.txt), which expressly permits self-hosting and redistribution. The
copyright holders are:

  Bricolage Grotesque   Copyright 2022 The Bricolage Project Authors
                        https://github.com/googlefonts/bricolage
  Instrument Sans       Copyright 2023 The Instrument Sans Project Authors
                        https://github.com/Instrument/instrument-sans
  JetBrains Mono        Copyright 2020 The JetBrains Mono Project Authors
                        https://github.com/JetBrains/JetBrainsMono

Updating
--------
Refetch from the Google Fonts css2 API with a modern browser User-Agent to get
woff2, then regenerate assets/css/fonts.css to match. Keep the unicode-range
values exactly as Google supplies them.
