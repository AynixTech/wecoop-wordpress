# CV Templates (HTML + CSS)

This folder contains static HTML/CSS templates to render CVs.

## Templates
- vibrant
- formal
- matrix
- peach

## Files
- `vibrant.html` + `vibrant.css`
- `formal.html` + `formal.css`
- `matrix.html` + `matrix.css`
- `peach.html` + `peach.css`

## Placeholder tokens
Use string replacement before PDF rendering.

- `{{full_name}}`
- `{{job_title}}`
- `{{email}}`
- `{{phone}}`
- `{{address}}`
- `{{profile_summary}}`
- `{{skills_list}}`
- `{{languages_list}}`
- `{{experience_list}}`
- `{{education_list}}`
- `{{photo_url}}`

## Notes
- Each HTML references its local CSS file in this same folder.
- Tokens `{{experience_list}}`, `{{education_list}}`, `{{skills_list}}`, `{{languages_list}}` should be pre-rendered as HTML lists/blocks.
