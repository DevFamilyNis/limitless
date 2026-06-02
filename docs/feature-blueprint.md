# Feature Blueprint Protocol

This document defines the mandatory workflow for implementing **any new feature**
in this codebase. It is referenced from `CLAUDE.md` and applies to every feature,
without exception, regardless of how small it appears.

When implementing a new feature, **do not start coding immediately.**
Every feature must first go through a short, structured blueprint phase.

The goal of the blueprint is **not** to audit the whole application.
The goal is to confirm that the feature is clearly described, placed correctly,
and planned layer by layer before any code is changed.

Do not search broadly through the codebase during the first blueprint response
unless the user explicitly asks for it.

---

## 1. Feature Intake

For every new feature, first confirm the feature definition.

The feature definition should include:

- Feature name
- Business purpose
- Where the feature belongs in the application
- Who can access it
- Who can create it
- Who can edit it
- Who can delete it
- Which records or states are immutable
- Any special business rules provided by the user
- Any known constraints provided by the user

If the user already provided enough information, summarize it.
If something important is missing, ask only the minimum required questions.

- Do not ask broad or generic questions.
- Do not start implementation.

---

## 2. Blueprint Mode

Before writing code, create a Feature Blueprint.
The blueprint must be practical and layer-based.

Use this structure:

```
Feature Blueprint: [Feature name]

Feature summary:
...

Business purpose:
...

Placement:
...

Access rules:
...

Allowed actions:
...

Forbidden actions:
...

Known constraints:
...

Layer plan:
1. Business rules
2. Database
3. Model / relations / enums
4. DTO / Action
5. Authorization
6. Validation
7. Routes / Controller
8. Livewire / Blade UI
9. Translations
10. Tests
11. Verification

Skipped layers:
- [Layer name]: [reason]

Open questions:
- [Only questions required before implementation]
```

Rules for Blueprint Mode:

- Do not include broad risk analysis.
- Do not investigate unrelated parts of the application.
- Do not write code during Blueprint Mode.

---

## 3. Layer Planning Rule

For each layer, explain only what is needed for **this** feature.

Use this format:

```
Layer X: [Layer name]

Purpose:
...

Planned work:
...

Depends on:
...

Output:
...

Needs approval:
Yes
```

Keep each layer short and specific.

- Do not include theoretical risks.
- Only mention constraints that directly affect the current feature.

---

## 4. Pause and Confirm Rule

Implementation must happen **one layer at a time**.
This pause-and-confirm step is mandatory and is **not** skipped for "small" or
"simple" features. It is the primary safety layer of this protocol: it applies
uniformly so that nothing reaches the codebase without explicit human approval.

Before implementing a layer, show the layer plan and wait for user approval.

Use this format:

```
Ready for Layer X: [Layer name]

I will:
- ...
- ...
- ...

I will not:
- ...
- ...

Confirm to implement this layer.
```

Do not continue until the user **explicitly** confirms.

Accepted confirmations include:

- `yes`
- `da`
- `nastavi`
- `može`
- `implementiraj`
- `odobreno`
- `next`
- `next layer`

If the user changes the scope, update the blueprint before continuing.

---

## 5. After Each Layer

After completing a layer, stop and summarize.
**Never** continue automatically to the next layer.

Use this format:

```
Layer X completed.

Changed:
- ...

Not changed:
- ...

Verification:
- ...

Next layer:
Layer Y: [Layer name]

Confirm to continue.
```