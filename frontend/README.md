# ORPAIS Next.js frontend

The `/activities` route displays active activities from the existing PHP/MySQL
site through `api/activities.php`. The original PHP page remains available at
`/activities.php` as a fallback.

## Run locally

1. Start Apache and MariaDB with XAMPP.
2. From this directory, run `npm run dev`.
3. Open [http://localhost:3000/activities](http://localhost:3000/activities).

The activities API defaults to
`http://localhost/ORPAICS/api/activities.php`. Configure another PHP
installation or public deployment by setting these server-side variables in
`.env.local`:

```dotenv
ORPAIS_ACTIVITIES_API_URL=http://localhost/ORPAICS/api/activities.php
ORPAIS_SITE_BASE_URL=http://localhost/ORPAICS/
```

`ORPAIS_SITE_BASE_URL` is used for the PHP fallback link when the API is
unavailable. The API also returns the canonical PHP base URL for activity
details and navigation.

Create a production build with `npm run build`, then serve it with `npm start`.
