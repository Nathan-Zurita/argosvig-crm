# CHANGELOG for 2.2

This changelog consists of the bug & security fixes and new features being included in the releases listed below.

## **v2.2.9 (Upcoming)**

* [enhancement] Added shift-based timekeeping review, daily results and import coverage, with a local demonstration dataset.

* [enhancement] Added image previews, zoom, and individual removal before saving incident attachments.

* [feature] Added multiple image attachments up to 20 MB each and a private gallery to operational incidents, with support for adding more images later.

* [fixed] Contact rows now display clean names and initials with direct links in both layouts.

* [enhancement] Improved mobile opportunity browsing, localized default commercial labels, prioritized actionable dashboard metrics, and made filters, quotes, employee lists, activities, and account settings easier to use across screen sizes.

* [fixed] Campaign and event names display correctly and open their editors.

* [fixed] Tag names open their editor without displaying HTML or a duplicate edit button.

* [fixed] Type and source names open their editors without displaying HTML or duplicate edit buttons.

* [fixed] Activity titles display as links instead of showing HTML in the activity list.

* [enhancement] Aligned the commercial follow-up shortcut with the other page actions on desktop.

* [fixed] Group names open the editor directly without displaying markup or a duplicate edit button.

* [fixed] Improved the activity page header on narrow screens.

* [fixed] Improved keyboard access to user editing and mobile search controls.

* [enhancement] Simplified opportunity cards by default and made table actions and card settings accessible to keyboard users.

* [fixed] Improved opportunity board sizing on phones, fixed clearing its search, and made the theme switch accessible by keyboard.

* [enhancement] Removed duplicate edit actions from attribute, import, and webhook lists.

* [enhancement] Removed duplicate edit actions from email template, pipeline, role, and workflow lists.

* [enhancement] Simplified department, work site, and contract lists and improved the opportunity page header on small screens.

* [enhancement] Removed duplicate edit actions from activity, quote, and organization lists when the record name already opens the editor.

* [fixed] Contact names display correctly, account settings adapt to smaller screens, and key navigation actions are easier to use.

* [enhancement] Assistant PDFs keep table titles with initial records, repeat module names on continuation pages, and show upcoming deadlines and open incidents on the overview.

* [feature] The assistant can generate incident report PDFs with filters for dates, severity and recorded location.

* [feature] Added a scoped CRM overview PDF with database-backed totals and tables, and standardized the layout of assistant-generated documents.

* [feature] Added voice dictation to the AI assistant and an AI-assisted incident form that fills fields from a typed or dictated account.

* [fixed] Sign-in shows validation errors only on submit and moves from email to password when Enter is pressed.

* [enhancement] Redesigned the sign-in screen with animation and introduced a sharper vector Argosvig shield in the portal and navigation.

* [fixed] Contact details load with formatted phone numbers, and dashboard contact links open in the current tab.

* [enhancement] Added a search box to the selects that list records (employees, work sites, contracts, companies, users, roles, groups, departments, pipelines and attribute options) and to every select with many options, filtering as the user types, ignoring accents, and keeping the look of the CRM fields in light and dark mode.

* [feature] Added the operations checklist board: cards with checklist items move between columns, keep who checked each item and who completed the card, and completed checklists can be searched by title, item, employee or work site.

* [feature] Added the checklist board settings, where every column can be added, renamed, recoloured, reordered and removed, and marked to complete the cards entering it; cards of a removed column move to the first column that does not complete cards.

* [feature] Added a private S3-compatible bucket as the storage for HR documents (FILESYSTEM_DOCUMENTS_DISK), the hr:move-documents command to move stored files there after checking each copy, and the bucket to the production readiness check.

* [feature] Added the internal procedure and blank form document types, which need no company or contract link.

* [feature] Added file attachments to the AI assistant (PDF, Word, Excel, CSV, text and images) to summarize, compare with the CRM or fill in a record from a document; files can be attached with the clip, by dragging them onto the panel or by pasting them.

* [feature] Added bulk creation from an attached spreadsheet for companies, contacts, products, employees, departments and sites, with a preview of new, existing and invalid rows before one-click confirmation.

* [feature] Added plain-language search in the AI assistant, such as companies in a state without an active contract, limited to the fields and records the person may see.

* [feature] Added AI suggestions by page: e-mail summary, reply, opportunity and activity; opportunity next step and proposal; incident severity and action plan.

* [feature] Added AI help in forms: severity and action plan while writing an incident, and type, name and expiration date read from an employee document being uploaded.

* [feature] Added AI actions to workflows: an e-mail received trigger that turns commercial messages into opportunities, and opportunity summary and next-step notes.

* [fixed] Translated the workflow entity and event names, which were shown in English.

* [feature] Reworked the AI settings: choose ChatGPT, Gemini or Claude, paste its key and pick a model from a list describing what each one is good at, with its reasoning, speed and cost, plus the transcription model and an in-page connection test.

* [feature] Reworked the AI assistant into a side panel with suggested tasks, a conversation history, copy and retry, review cards for prefilled records and files, and the Ctrl + J shortcut; its header button is now called Assistant.

* [fixed] Updated the default AI models to the current generation and gave reasoning models enough output room to answer.

* [feature] Removed OpenRouter; the CRM now connects only directly to OpenAI, Google and Anthropic.

* [feature] Added Argosvig AI settings with direct OpenAI, Claude and Gemini connections.

* [enhancement] Rebranded the installer, console commands, and project documentation for Argosvig.

* [security] Removed external installation reporting and the public remote logo endpoint.

* [fix] Allowed enough output tokens to test reasoning models in the OpenRouter connection check.

* [fix] Fixed saving the OpenRouter key from the Magic AI settings form.

* [enhancement] Added quote draft lines using catalog SKUs and current prices.

* [enhancement] Added scoped module totals to CRM assistant requests made from record listings.

* [enhancement] Opened editable activity drafts for calls, meetings and notes through the CRM assistant.

* [enhancement] Refined CRM assistant requests with short in-page conversations.

* [enhancement] Filled assistant draft relationships by exact name, code or employee number when an authorized match is unique.

* [feature] Added a contextual CRM assistant with draft forms and downloadable PDF, text, Markdown, CSV and Excel files.

* [feature] Unified OpenRouter setup for CRM AI and added a connection test with encrypted key storage.

* [fixed] Kept the dashboard revenue cards and chart legend within narrow mobile screens.

* [feature] Grouped the sidebar into Human Resources and Operations Management, with the entries of a group opening in a floating panel beside the item.

* [feature] Moved mail and the two configuration areas to the header, renamed as Administration and System preferences with a short description of each.

* [fixed] Fixed human resources menu entries never appearing for custom roles, since they did not match any permission.

* [fixed] Fixed long sidebar labels being cut off, the collapsed sidebar not naming its icons, the mobile menu hiding its expand arrows and the header overflowing small screens.

* [fixed] Fixed the error pages showing an example support address and untranslated links, and the marketing events and campaigns labels in English.

* [feature] Moving between admin pages now swaps the content without reloading the whole page, keeping the back and forward buttons working.

* [fixed] Fixed form validation messages showing a generic English text instead of Brazilian Portuguese.

* [fixed] Fixed form fields being almost invisible in the dark theme.

* [fixed] Fixed breadcrumb links with low contrast in the dark theme and the theme switch updating only one of the logos.

* [fixed] Fixed the monthly contract value overflowing its dashboard card.

* [fixed] Fixed the user name in the users list not opening the user, which now opens the edit modal.

* [fixed] Fixed schedules accepting an employee already scheduled at the same time or on vacation, and vacation periods overlapping each other.

* [fixed] Fixed a CPF typed without the mask skipping the duplicate employee check.

* [fixed] Fixed human resources and commercial follow-up pages ignoring the configured admin path and the admin language.

* [fixed] Added the missing Brazilian Portuguese texts in web forms, email templates and the lookup search.

* [feature] Added a contract operations PDF for review before sharing with customers.

* [feature] Added shift preparation from replacement suggestions with an availability check before saving.

* [feature] Added daily shift handover notifications for authorized operations staff.

* [feature] Added contract-specific handovers for client reviews and renewal preparation.

* [feature] Added imported punch checks and upcoming document and contract deadlines to the operational handover.

* [feature] Added staffing gaps for the next 48 hours and replacement suggestions checked against shifts and vacations.

* [feature] Added AI-assisted incident drafts from text and recorded audio with review before saving.

* [feature] Added an optional AI assistant that answers operational questions using authorized handover records.

* [feature] Added a cross-module shift handover with incident and schedule records and source links.

* [security] Enabled SMTP certificate verification and production TLS, and added an explicit SMTP delivery test command.

* [security] Added optional authenticator-based two-factor login with one-time recovery codes for staff accounts.

* [feature] Added a company migration preview and transactional import with persistent source-to-CRM IDs.

* [feature] Added a production readiness check and a database and attachment backup script with integrity checks.

* [feature] Added configurable entry requirements for opportunity pipeline stages.

* [security] Restricted operational dashboard figures to the permitted modules and document categories for globally scoped users.

* [feature] Added a 30-day contract expiry filter and linked renewal drafts that preserve the previous contract.

* [feature] Added quote send history, acceptance and decline recording, and linked contract drafts from accepted versions.

* [feature] Added commercial follow-up for open opportunities without upcoming actions and overdue activities.

* [feature] Added a missing document report by type and payroll month, and scheduled private document expiry reminders.

* [feature] Added a private document center with payroll months, expiration filters, activity history and files linked to companies or contracts.

* [feature] Added attendance mark imports, employee and date filters, and a provider interface for future Alterdata API synchronization.

* [feature] Added department and employee management with access control and Alterdata employee identifiers.

* [feature] Added employee profiles with private documents, expiration tracking and protected downloads.

* [feature] Added customer service contract management with lifecycle, ownership, term and value tracking.

* [feature] Added the Argosvig operational overview and promoted workforce, contracts and departments to visible navigation areas.

* [feature] Added operational incident management with severity, assignment, investigation and resolution tracking.

* [feature] Added contract-linked operational work site management and staffing requirements.

* [feature] Added employee shift assignments by work site with schedule and attendance status.

* [fixed] Standardized operational module contrast in light and dark themes and prioritized daily-use navigation.

* [fixed] Included operational package views in the Tailwind build and restored the dashboard hero, quick actions and responsive KPI grid.

## **v2.2.8 (5th of Nov 2026)**

* [feature] Added the Argosvig white-label identity across authentication, navigation, support content, email defaults, and compiled admin assets.

* [fixed] Made locale initialization compatible with PHP 8.4 without dynamic middleware properties.

* [security] Fixed Sercurity issue.

## **v2.2.7 (16th of Oct 2026)**

* [fixed] Fixed a web form submission creating a duplicate contact when the address differed only in case.

* [fixed] Fixed an empty web form submission answering with a server error.

* [feature] Added quotes to Quick Add.

* [fixed] Fixed creating a quote without line items failing with `Undefined array key "items"`. 

* [fixed] Fixed the leads settings control moving when switching between the kanban and table views.

* [feature] Added notifications.

* [feature] Update data filter at dashboard.

* [feature] Mega Search Will open into modal.

* [feature] Added a per-section record count to the mega search. 

* [fixed] Fixed the lookup field's search field.

* [fixed] Fixed saving a contact with the phone left blank answering with a server error.

* [fixed] Fixed picking a lead on a quote leaving the quote unsaveable and unlinked from that lead.

* [fixed] Fixed picking a lead on a quote replacing the existing items instead of adding to them.

* [fixed] Fixed a lead's page erroring when its contact had no phone number.

* [fixed] Fixed removing a quote from a lead failing with no such lead.

* [fixed] Fixed a lead and its quote not holding the same products.

* [fixed] Fixed opening a mail outside drafts erroring under any locale but English.

* [fixed] Added the missing Spanish and Brazilian Portuguese translations.

* [fixed] Fixed the language file test passing while a translation was missing.

* [security] Improved in Security.

## **v2.2.6 (10th of Sept 2026)**

* [security] Improved in Security.

* [feature] Added MariaDB support.

* [feature] Added customizable lead card information. Datagrid columns and Kanban lead card fields can now be chosen per user through new column and card settings components.

* [feature] Added PDF export for dashboard reports, available from the dashboard alongside the existing views.

* [feature] Added Japanese (`ja`) translation for the Admin, Installer, GoogleContact and WebForm packages.

* [feature] Added an associated group column to the users grid in Settings > Users.

* [fixed] Fixed duplicated activity handling across the lead, person, product and warehouse activity controllers by consolidating the shared logic, and added the missing Japanese activity translations.

* [fixed] Fixed the data transfer import queue processing inconsistently, and corrected the form control group rendering used by the import screen.

* [fixed] Fixed the Google Contact settings screen erroring when no account was connected, and added the corresponding translations.

* [fixed] Fixed the mail ACL mapping so email actions are checked against the correct permission.

* [fixed] Fixed people created from a lead being saved with a null `user_id`, which hid them from the person listing for users restricted to group or individual data scope. They are now assigned to the lead owner, falling back to the acting user.

* [fixed] Fixed the stage API resource omitting `lead_pipeline_id`, so stages could not be matched to their pipeline.

* [fixed] Fixed email attachment downloads being blocked by the URL sanitizer middleware.

* [fixed] Added the missing Chinese translations for the users grid's associated group column.

* [fixed] Fixed flaky admin end-to-end tests around organization owner lookup, lead creation and rich-text comment fields.

* [fixed] Fixed creating or updating a lead failing with a server error (HTTP 500) when a product row was submitted without a `product_id`. The request is now rejected with a validation error.

* [fixed] Fixed the expected close date of a lead being cleared when its stage was changed (Kanban drag and drop, the stage bar, won/lost) or another field was edited inline from the lead view.

* [security] Fixed user and role listings not being scoped by the acting user's data scope, which allowed users to see records outside their own group. Roles now track their creator via a new `created_by` column.

* [security] Hardened the admin ACL middleware to fail closed. An administrative route with no ACL mapping is now denied rather than allowed, inheriting the permission of its nearest mapped ancestor, with a narrow allow-list for authentication, self-service account management and generic UI helpers.

* [security] Removed SVG from the allowed upload types for the admin logo and favicon configuration fields.

* [security] Fixed a security issue configuration file uploads.

* [security] Fixed a security issue activity notes and the TinyMCE editor component.

* [security] Fixed a security issue in the web form embed view.

* [security] Fixed a security issue in attribute downloads.

* [security] Fixed a security issue in mail links.

* [security] Fixed broken tag handling in the tag settings controller.

* [security] Fixed SVG sanitization bypasses in media and configuration file uploads.

* [security] Secured installer APIs.

## **v2.2.5 (4th of Aug 2026)**

* #2631[fixed] Fixed the persons CSV import creating duplicate records and dropping select attribute values on re-import. Existing people are now updated by matched email regardless of a changed phone or organization (so the reported count is accurate), and select/multiselect option labels (or ids) are resolved to their option ids instead of being stored as `0`.

* #2630[fixed] Fixed date attributes in the persons CSV import silently saving as `0000-00-00`. Spreadsheet serial numbers and regional formats such as `DD/MM/YYYY` are now normalised to a valid date, and a value that cannot be parsed is reported as a row error instead of being stored as a zero date.

* [feature] Added Chinese (Simplified) `zh_CN` translation for the Admin, Installer, DataTransfer, WebForm and Core packages.

* [feature] Added a configurable default dashboard date range — 1 month, 3 months, 9 months, 1 year, 2 years or a custom number of days — under Configuration > General > Settings > Dashboard Configurations.

* [fixed] Fixed menu item names set in Configuration not applying to section pages, breadcrumbs and the mobile sidebar. Previously only the desktop sidebar reflected a rename.

* [fixed] Fixed renaming the "Mail" and "Contacts" menu items having no effect anywhere, as their configuration fields did not match the actual menu keys.

* [fixed] Fixed the dashboard date range label omitting the year on ranges spanning more than one calendar year, which rendered as "30 Jul - 30 Jul".

* [fixed] Fixed Arabic DataTransfer translations never loading, as the file was named `ar/ar.php` instead of `ar/app.php`.

* [fixed] Fixed the missing Korean translation for the "None" input validation option on the create and edit attribute forms.

* [enhancement] Moved the Core and DataTransfer package translations into the Admin package. Only packages that ship their own Blade views now carry a `Resources/lang` directory.

* [enhancement] Reduced database queries on every admin page by loading the configured menu names in a single query instead of one per menu item.

* [enhancement] Documented the localization convention in the `crm-package-development` agent skill and in AGENTS.md.
* [feature] Added a collapse/expand toggle to the admin sidebar, matching the Bagisto admin. The choice is remembered across page loads, and page content now reflows to the sidebar width instead of being overlaid by it. The sidebar no longer expands on hover; it is controlled by the toggle only.
* #2614[security] Fixed unauthenticated installer access and executable email attachment upload vulnerabilities.

* #2612[feature] Added import and export support for custom attributes for Leads and Persons.

* #2609[feature] Added Google Contacts export for Persons with Google account connection, duplicate detection, queued export progress, and result summary.

* #2608[fixed] Added missing "none" key to the Korean locale for attribute validation.

* #2606[feature] Added a collapse/expand toggle to the admin sidebar, matching the Bagisto admin. The choice is remembered across page loads, and page content now reflows to the sidebar width instead of being overlaid by it. The sidebar no longer expands on hover; it is controlled by the toggle only.

* #2606[feature] Added an option to show or hide the "Powered by" bar under Configuration > General > Settings > Powered by Section Configurations.

* #2603[feature] Added Korean translations for the Installer, DataTransfer, WebForm, and Core packages.

* #2602[feature] Added Korean translation support for the Admin package.

* #2600[fixed] Fixed invalid activity calendar .ics date-times by emitting UTC RFC 5545 values.

* #2592[security] Fixed webhook validation to reject internal endpoint URLs.

* #2580[enhancement] Added a "None" option to input validation for text attributes.

## **v2.2.4 (20th of July 2026)** *Release*

* #2590[fixed] Fixed page does not refresh after creating a record via Quick Add.

* #2589[fixed] Fixed Quick Add not working for users with group and individual permissions.

* #2582[fixed] Fixed pipeline field visible on public webform.

* #2581[enhancement] Fixed responsive UI issues when page is zoomed.

* #2579[feature] Allow group selection for individual view permission users.

* #2575[enhancement] Added previous month's sales update in Kanban view.

* #2573[enhancement] Added dashboard support for multiple pipelines.

* #2572[enhancement] Added filter by tag option in Contacts > Persons.

* #2571[fixed] Fixed issue with lead creation.

* #2570[fixed] Fixed auto-fill lead email issue.

* #2567[fixed] Fixed IDOR agent record access control vulnerability.

* #2563[fixed] Fixed Kanban infinite scroll duplicates issue.

* #2583[fixed] Fixed SQL injection in rotten lead filter.

* #2585[security] Fixed unrestricted file upload vulnerability (CVE-2026-38526).

* #2559[fixed] Fixed agent record access control issue.

* #2556[fixed] Fixed installation config save issue.

* #2550[fixed] Fixed Kanban infinite scroll duplicates.

* #2549[enhancement] Added support tab feature.

* #2548[enhancement] Allow search by phone and email when creating a lead.

* #2546[feature] Quick Attribute now available at lead form.

* #2545[feature] Added agent skills functionality.

* #2544[enhancement] Added validate skills.

* #2543[enhancement] Added Agents Skills folder.

* #2542[fixed] Fixed stored XSS vulnerability in notes field.

* #2541[fixed] Fixed quote description truncation issue.

* #2539[fixed] Fixed lost revenue arrow UI issue.

* #2538[fixed] Fixed missing translations.

* #2501[fixed] Fixed sales owner not saved in organization.

* #2500[fixed] Fixed activities date filter range issue.

* #2479[fixed] Fixed textarea field not rendered in WebForm.

* #2471[fixed] Fixed missing translations for lead won/lost modal.

* #2420[fixed] Added missing mega search translations for settings and configurations.

* #2533[fixed] Fixed GUI installation issue.

* #2419[security] Fixed stored XSS vulnerability in notes field.

* #2454[fixed] Fixed quote description truncation.

* #2407[fixed] Fixed missing translations.

* #2157[fixed] Fixed auto-fill lead email when creating a lead.

* #2258[fixed] Fixed issue with same-as-billing-address field.

## **v2.2.3 (1st of May 2026)** *Release*

* [fixed] Pipline critical issue resolved.

## **v2.2.2 (1st of May 2026)** *Release*

* [fixed] Update Change Log and version.

## **v2.2.1 (1st of May 2026)** *Release*

* [fixed] Quote fields now auto-fill correctly when a quote is linked to a lead.

* [fixed] Fixed price formatting issue.

* [fixed] Fixed Lead Kanban list ordering.

* [fixed] Fixed header block position at the top.

* [fixed] Updated Activity UI.

* [fixed] Admins can now view and share quote details to a person from the quote list.

* [fixed] Fixed submission issue on the web form.

* [fixed] Fixed activity display issue in the Calendar view.

* [fixed] Logo update issue resolved.

* [enhancement] Drag-and-drop support added to Activity. Admins can now change date and time directly from the Calendar view.

* [feature] Quick App feature added for faster access to key CRM actions.

* [feature] Admins can now add or update a person directly from the lead view page.

* [security] Resolved an authentication bypass vulnerability caused by improper access control in the installer.

## **v2.2.0 (17th of March 2026)** *Release*

* **[Laravel 12 Upgrade]** Upgraded framework to Laravel 12

* #2480[enhancement] Codebase updates and refinements.

* #2478[enhancement] Improved class instantiation handling.

* #2472[enhancement] Upgrade to Laravel 12.

* #2470[enhancement] Updated auto_commits.yml configuration.

* #2469[enhancement] General enhancements and optimizations.

* #2468[enhancement] Documentation updates (MD files).

* #2450[fixed] Added ACL support for warehouses.

* #2444[fixed] Improved global search functionality for organizations.
