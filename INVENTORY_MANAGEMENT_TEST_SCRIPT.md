# Inventory Management QA Test Script

## Scope

Validate the asset inventory workflow for Assets, Equipment, Accessories, and the grouped Asset Details resources.

## Preconditions

- Sign in as a user allowed to view and edit inventory records.
- Have at least one Equipment asset and one Accessory asset.
- Have at least one Brand, Equipment Type, Model, Tag, and Location.
- Have at least one Equipment Model with no Brand and/or no Equipment Type.
- Have an Equipment Model used by at least one Equipment and one Accessory.
- Keep the database backup available before testing destructive actions.

## Navigation and Resource Access

| ID     | Test                | Steps                                                                  | Expected result                                                                                            |
| ------ | ------------------- | ---------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| NAV-01 | Assets link         | Open the `Assets` navigation link.                                     | `/asset` opens and shows both Equipment and Accessories.                                                   |
| NAV-02 | Equipment link      | Open the `Equipment` navigation link.                                  | `/equipment` opens and shows Equipment only. The Asset Type column is hidden.                              |
| NAV-03 | Accessories link    | Open the `Accessories` navigation link.                                | `/accessories` opens and shows Accessories only. The Asset Type column is hidden.                          |
| NAV-04 | Navigation counts   | Compare the Equipment and Accessories badges with the visible records. | Each count matches the records shown by its page.                                                          |
| NAV-05 | Asset Details group | Open each grouped resource: Location, Tag, Type, Brand, and Model.     | Each resource opens successfully and its list, create, edit, and view pages work according to permissions. |

## Asset List and Detail Views

| ID     | Test                   | Steps                                                  | Expected result                                                                                                                                        |
| ------ | ---------------------- | ------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| AST-01 | Equipment list columns | Open `/equipment`.                                     | Equipment Code, Name, Status, Lifecycle Status, Model, Type, and other applicable columns display correctly. `Allocated to` and Asset Type are hidden. |
| AST-02 | Accessory list columns | Open `/accessories`.                                   | Accessory records display correctly. `Allocated to` is available where applicable. Asset Type is hidden.                                               |
| AST-03 | All-assets columns     | Open `/asset`.                                         | Both asset types display and Asset Type is visible.                                                                                                    |
| AST-04 | Equipment detail       | Open an Equipment record.                              | Asset details show Model, Brand, Type, Location, Lifecycle Status, Tags, and applicable fields.                                                        |
| AST-05 | Accessory detail       | Open an Accessory record.                              | Accessory detail shows Allocated to Equipment and does not show equipment-only Location content where not applicable.                                  |
| AST-06 | Type link              | Click the Type value in an asset infolist.             | The Equipment Type view page opens for the selected type.                                                                                              |
| AST-07 | Model and Brand links  | Click Model, Brand, or other configured related links. | The corresponding resource view opens and displays the correct record.                                                                                 |

## Equipment and Accessory Relationships

| ID     | Test                       | Steps                                                                              | Expected result                                                                 |
| ------ | -------------------------- | ---------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| REL-01 | Allocate accessory         | From an Equipment view, allocate an Accessory to that Equipment.                   | The Accessory shows the Equipment in `Allocated to`; the relationship is saved. |
| REL-02 | Prevent invalid allocation | Attempt to allocate an Accessory to another Accessory or an Equipment to a parent. | The invalid relationship is rejected or normalized to no parent.                |
| REL-03 | Equipment Accessories tab  | Open an Equipment view and inspect Accessories.                                    | Related Accessories are listed with the correct count.                          |
| REL-04 | Location related tabs      | Open a Location view.                                                              | Equipment, Accessories, Children, and Descendants tabs display with counts.     |
| REL-05 | Tag related tabs           | Open a Tag view.                                                                   | Equipment, Accessories, Children, and Descendants tabs display with counts.     |
| REL-06 | Type related tabs          | Open an Equipment Type view.                                                       | Models, Equipment, and Accessories tabs display with counts.                    |
| REL-07 | Brand related tabs         | Open a Brand view.                                                                 | Models, Equipment, and Accessories tabs display with counts.                    |
| REL-08 | Model related tabs         | Open a Model view.                                                                 | Equipment and Accessories tabs display with counts.                             |

## Lifecycle Logs

| ID     | Test                              | Steps                                                                                       | Expected result                                                                                                    |
| ------ | --------------------------------- | ------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ |
| LOG-01 | Equipment deployment              | Open Equipment view, choose the deployment lifecycle action, select a Location, and submit. | Lifecycle status and cached `location_id` update; a lifecycle log is created with deployment location and remarks. |
| LOG-02 | Equipment idle/maintenance        | Change Equipment to Idle or Under Maintenance and select a Location.                        | Status, cached location, and lifecycle log update correctly.                                                       |
| LOG-03 | Accessory allocation log          | Allocate an Accessory to Equipment.                                                         | An allocation lifecycle log records the accessory, action, status, and target Equipment.                           |
| LOG-04 | Accessory unallocation log        | Remove one or more Accessories from Equipment.                                              | Each affected Accessory receives an Idle lifecycle log.                                                            |
| LOG-05 | Lifecycle log modal for Equipment | Open a lifecycle log from an Equipment record.                                              | `Deployed To` is visible; `Allocated To Equipment` is hidden.                                                      |
| LOG-06 | Lifecycle log modal for Accessory | Open a lifecycle log from an Accessory record.                                              | `Allocated To Equipment` is visible; `Deployed To` is hidden.                                                      |
| LOG-07 | Lifecycle log history             | Review the Lifecycle Logs tab.                                                              | Logs are sorted newest first and show Action, Status, applicable target, remarks, performer, and timestamp.        |

## Edit Logs

| ID      | Test                   | Steps                                                             | Expected result                                           |
| ------- | ---------------------- | ----------------------------------------------------------------- | --------------------------------------------------------- |
| EDIT-01 | Equipment edit log     | Edit an Equipment field such as Plate Number or Remarks and save. | An Edit Log is created with the old and new values.       |
| EDIT-02 | Multiple field changes | Change Brand, Type, Model, and another editable field, then save. | One edit operation records the changed fields accurately. |
| EDIT-03 | No-op save             | Open Edit, make no changes, and save.                             | No empty edit log is created.                             |
| EDIT-04 | Accessory edit log     | Edit an Accessory and save.                                       | The change is recorded against the Accessory.             |

## Model Brand and Type Synchronization

| ID     | Test                        | Steps                                                                  | Expected result                                                                              |
| ------ | --------------------------- | ---------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| MOD-01 | Model with Brand and Type   | Edit a Model and select both Brand and Equipment Type. Save.           | All related Equipment and Accessories receive the same Brand and Type IDs.                   |
| MOD-02 | Model with no Brand         | Clear Brand on a Model and save.                                       | All related Equipment and Accessories have `eqm_brand_id = null`; Type remains synchronized. |
| MOD-03 | Model with no Type          | Clear Equipment Type on a Model and save.                              | All related Equipment and Accessories have `eqm_eqmt_id = null`; Brand remains synchronized. |
| MOD-04 | Model with neither          | Clear Brand and Equipment Type and save.                               | All related Equipment and Accessories have both Brand and Type set to `null`.                |
| MOD-05 | Fill previously blank Brand | Select a Brand on a Model that previously had no Brand. Save.          | All related Equipment and Accessories receive the selected Brand.                            |
| MOD-06 | Fill previously blank Type  | Select an Equipment Type on a Model that previously had no Type. Save. | All related Equipment and Accessories receive the selected Type.                             |
| MOD-07 | Transaction rollback        | Simulate a failed related update in a controlled test environment.     | The Model change and related asset updates roll back together.                               |
| MOD-08 | Model form fields           | Open Model create/edit form.                                           | Only Model Name, nullable Brand, nullable Equipment Type, and Remarks are present.           |
| MOD-09 | Model infolist              | Open a Model view.                                                     | Model Name, Brand, Equipment Type, and Remarks display; Equipment Type is clickable.         |

## Delete and Constraint Checks

| ID    | Test                             | Steps                                                         | Expected result                                                                 |
| ----- | -------------------------------- | ------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| DB-01 | Delete Model with related assets | Attempt to delete a Model used by Equipment or Accessories.   | Delete is blocked by the restrictive foreign key. Related assets remain intact. |
| DB-02 | Delete unused Model              | Delete a Model with no related assets.                        | Delete succeeds if authorized.                                                  |
| DB-03 | Delete Equipment Type in use     | Attempt to delete an Equipment Type used by Models or Assets. | Delete is blocked by the restrictive foreign key.                               |
| DB-04 | Delete Brand in use              | Attempt to delete a Brand used by Models or Assets.           | Behavior matches the configured FK rule and does not cascade-delete assets.     |

## Work Orders

| Test Case ID | Test Case Name                    | Description                                                                              | Preconditions                                                                                          | Test Steps                                                                                                                                                                                                        | Expected Results                                                                                                                                                            | Tester Name | Test Result | Test Remarks |
| ------------ | --------------------------------- | ---------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------- | ----------- | ------------ |
| WO-01        | Open Work Orders from Assets      | Verify that an authorized user can access Work Orders from the combined Assets resource. | - The user is successfully logged into the system.<br>- An Equipment record exists.                    | 1. Click the **Assets** link in the left navigation menu.<br>2. Select an Equipment record.<br>3. Open the **Work Orders** tab.<br>4. Test the create, view, and edit actions.                                    | - The Work Orders tab opens successfully.<br>- Create, view, and edit routes work.<br>- The Work Order remains associated with the selected Equipment.                      |             |             |              |
| WO-02        | Open Work Orders from Equipment   | Verify that an authorized user can access Work Orders from the Equipment resource.       | - The user is successfully logged into the system.<br>- An Equipment record exists.                    | 1. Click the **Equipment** link in the left navigation menu.<br>2. Select an Equipment record.<br>3. Open the **Work Orders** tab.<br>4. Create or open a Work Order.                                             | - The Work Orders tab opens successfully.<br>- The Work Order opens under `/equipment/{equipment}/work-orders/...`.<br>- The Work Order references the correct Equipment.   |             |             |              |
| WO-03        | Open Work Orders from Accessories | Verify that an authorized user can access Work Orders from the Accessories resource.     | - The user is successfully logged into the system.<br>- An Accessory record exists.                    | 1. Click the **Accessories** link in the left navigation menu.<br>2. Select an Accessory record.<br>3. Open the **Work Orders** tab.<br>4. Create or open a Work Order.                                           | - The Work Orders tab opens successfully.<br>- The Work Order opens under `/accessories/{equipment}/work-orders/...`.<br>- The Work Order references the correct Accessory. |             |             |              |
| WO-04        | Work Order relationship           | Verify that a Work Order created from an asset is linked to the correct parent resource. | - The user is successfully logged into the system.<br>- An Equipment or Accessory record is available. | 1. Open an Equipment or Accessory record.<br>2. Open the **Work Orders** tab.<br>3. Click **New Work Order**.<br>4. Complete the form and submit it.<br>5. Reopen the Work Order and return to the parent record. | - The Work Order is successfully created.<br>- The Work Order references the correct asset.<br>- The Work Order returns to the correct parent resource configuration.       |             |             |              |

## Automated Regression Command

Run the existing focused test after manual changes:

```powershell
php artisan test --compact tests\Feature\EquipmentAccessoryAllocationTest.php
```

Expected result: all tests pass.

## Test Result Summary

- Tester:
- Date:
- Environment:
- Build/commit:
- Passed:
- Failed:
- Blocked:
- Notes:
