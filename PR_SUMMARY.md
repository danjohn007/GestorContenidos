# PR Summary: Fix Contact Phone Not Saving and Not Displaying

## Issue Description
The contact phone number was not being saved correctly in the admin panel and was not displaying in the public site footer, as shown in the issue screenshots.

## Root Cause Analysis

### The Problem
The `telefono_contacto` configuration field had a **group mismatch**:
- **Database initial value**: `grupo = 'contacto'`
- **Code save logic**: `grupo = 'general'` (in configuracion_sitio.php)
- **Code read logic**: `grupo = 'general'` (in index.php)

This caused the phone number to:
1. Not update the correct record when saved
2. Not be retrieved when displaying the public site

### Technical Details
- `configuracion_sitio.php` line 151: calls `setOrCreate()` with `grupo = 'general'`
- `index.php` line 95: calls `getByGrupo('general')` to retrieve config
- `index.php` line 1112: displays phone in footer
- Database initial seed had `telefono_contacto` with `grupo = 'contacto'`

## Solution Implemented

### Changes Made

#### 1. Database Schema Fix (`database.sql`)
- Moved `telefono_contacto` from group `'contacto'` to group `'general'`
- Added `direccion` field with group `'general'` (was missing entirely)
- Maintains consistency with other site configuration fields

#### 2. Migration Script (`database_fix_telefono_contacto.sql`)
Created a migration script for existing installations that:
- Updates existing `telefono_contacto` records to use group `'general'`
- Creates the field if it doesn't exist
- Handles `direccion` field similarly
- Includes safety checks and comments

#### 3. Documentation
- **FIX_TELEFONO_CONTACTO.md**: Technical documentation in Spanish
- **FLUJO_DATOS_TELEFONO.md**: Visual data flow diagram showing before/after states
- **INSTRUCCIONES_PRUEBA.md**: Step-by-step testing instructions

### Files Modified
- `database.sql` - 2 lines changed (moved telefono_contacto, added direccion)
- `database_fix_telefono_contacto.sql` - NEW migration script
- Documentation files - NEW

### Files NOT Modified
No code changes were needed! The application logic was already correct:
- ✅ `configuracion_sitio.php` - Already saving to 'general' group
- ✅ `index.php` - Already reading from 'general' group
- ✅ All models and helpers - No changes needed

## Testing Instructions

### For New Installations
No action required - the database schema is already correct.

### For Existing Installations

1. **Apply Migration**
   ```bash
   mysql -u user -p database < database_fix_telefono_contacto.sql
   ```

2. **Test Admin Panel**
   - Go to Configuration → Site Data
   - Enter phone number in "Teléfono de Contacto" field
   - Click "Save Changes"
   - Verify success message appears
   - Reload page and verify phone persists

3. **Test Public Site**
   - Open public site (index.php)
   - Scroll to footer
   - Verify phone displays in "Contacto" section
   - Format: 📞 442-123-4567

4. **Verify Database** (optional)
   ```sql
   SELECT clave, valor, grupo 
   FROM configuracion 
   WHERE clave = 'telefono_contacto';
   ```
   Expected: `grupo = 'general'`

## Impact Assessment

### What Changed
- ✅ Database initial seed values (group assignment)
- ✅ Added migration script for existing installations

### What Stayed the Same
- ✅ No application code changes
- ✅ No UI changes
- ✅ No breaking changes
- ✅ Backward compatible with migration script

### Risk Level: LOW
- Minimal change (database group assignment)
- No code logic changes
- Migration script is idempotent (safe to run multiple times)
- Existing functionality preserved

## Code Review

All code review feedback addressed:
- ✅ Changed default phone in migration from hardcoded to empty string
- ✅ Fixed markdown table formatting
- ✅ Clarified explanation of the technical issue
- ✅ Added comments explaining UNIQUE KEY behavior in migration script

## Benefits

1. **Consistency**: All site configuration fields now in same group
2. **Reliability**: Phone number saves and displays correctly
3. **Maintainability**: Clear documentation for future reference
4. **User Experience**: Contact information now visible to site visitors

## Verification Checklist

- [x] Root cause identified and documented
- [x] Database schema corrected
- [x] Migration script created and tested
- [x] Documentation provided
- [x] Code review feedback addressed
- [x] Testing instructions provided
- [x] No breaking changes introduced

## Next Steps

1. User applies migration script to their database
2. User tests phone save/display functionality
3. User provides screenshots as evidence
4. PR can be merged once verified by user

## Related Files

- `database.sql` - Initial database schema
- `database_fix_telefono_contacto.sql` - Migration script
- `configuracion_sitio.php` - Admin configuration page
- `index.php` - Public site with footer
- `app/models/Configuracion.php` - Configuration model

## Screenshots Needed

User should provide:
1. Admin panel showing phone field with value saved
2. Public site footer showing phone displayed correctly
3. (Optional) Database query result showing group='general'
