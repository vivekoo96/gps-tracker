# Vendor Portal Access Guide

To access specific vendor portals (like GHMC) on your EC2 instance, you need to use subdomains. The system uses the subdomain to identify which vendor is logging in and shows their specific logo.

## 1. Local Testing / No DNS
If you haven't set up a real domain yet, you can use `nip.io`. It automatically points any subdomain of an IP back to that IP.

**Example for GHMC:**
Instead of `http://3.110.102.116`, use:
`http://ghmc.3.110.102.116.nip.io`

When you visit this URL, the login page will automatically show the **GHMC Logo**.

## 2. Using a Real Domain
If you have a domain (e.g., `gps-tracker.com`), you should:
1. Create an `A` record for `gps-tracker.com` pointing to `3.110.102.116`.
2. Create a wildcard Record `*` pointing to `3.110.102.116`.

Then you can access:
`http://ghmc.gps-tracker.com`

## Summary of Branding
- **Default Logo:** Shown when accessing via the main IP or an unrecognized subdomain.
- **Vendor Logo:** Shown when accessing via the vendor's assigned subdomain.
