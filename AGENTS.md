# Project Instructions

## Database changes

- This web application shares the team2026 database with the game server.
- For every schema, seed, or managed-data change, create a timestamped migration in C:/Users/Admin/Downloads/nroteam/dragonball_2026/sql/migrations/.
- Append the exact same migration to C:/Users/Admin/Downloads/nroteam/dragonball_2026/sql/teamobi2026.sql between named migration markers.
- Do not create new standalone SQL dumps under down/ for application updates.
- Existing databases receive only the new migration; empty databases receive only the canonical bundle.
- Keep credentials in config.local.php; never put secrets into migrations or tracked configuration.
