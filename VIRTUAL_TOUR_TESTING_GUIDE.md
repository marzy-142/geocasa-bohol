# 🧪 How to Test the Virtual Tour Feature

## ✅ Test Setup Complete!

Your virtual tour feature is now ready to test with a **local panorama image**.

### What Was Set Up:

1. ✅ **Panorama image downloaded** to: `storage/app/public/properties/virtual-tours/test-panorama.jpg`
2. ✅ **Beachfront Paradise property updated** with virtual tour enabled
3. ✅ **Storage link** already exists for public access

---

## 🎯 How to Test It

### Step 1: Open the Property Page

1. Make sure your dev server is running (`npm run dev`)
2. Visit: **http://localhost** (or your dev URL)
3. Go to **"Properties"** page
4. Click on **"Beachfront Paradise - Panglao Island"**

### Step 2: Look for the Tabs

You should see **TWO tabs** at the top of the media section:

-   📷 **"Photo Gallery"** (with badge showing "3" photos)
-   🌐 **"360° Panorama"** (with purple "NEW" badge)

### Step 3: Click the Panorama Tab

1. Click the **"360° Panorama"** tab
2. You should see:
    - Purple gradient header with "360° Virtual Tour"
    - Text: "Drag to explore • Scroll to zoom"
    - "🌟 Interactive" badge
    - The panoramic image viewer below

### Step 4: Interact with the Panorama

If everything works correctly, you should be able to:

-   ✅ **Drag with your mouse** to look around in all directions
-   ✅ **Scroll** to zoom in and out
-   ✅ **See a beach/landscape panorama** (not an error message)
-   ✅ Navigate controls at the bottom (zoom, fullscreen)

---

## ❓ What Makes It a "Virtual Tour"?

A property is considered to have a **Virtual Tour** when:

### 1. Database Fields Are Set:

```php
has_virtual_tour = true  // Boolean flag
virtual_tour_images = ["path/to/panorama.jpg"]  // Array of panorama images
```

### 2. UI Shows Both Features:

-   **Photo Gallery tab** - Regular property photos
-   **360° Panorama tab** - Interactive panoramic viewer
-   Both tabs are visible and switchable

### 3. Panorama Viewer Works:

-   Photo Sphere Viewer loads successfully
-   Image is in equirectangular format (2:1 aspect ratio)
-   User can drag, zoom, and explore in 360°

---

## 🔍 Troubleshooting

### If Panorama Still Shows Error:

**Check 1: Clear Browser Cache**

```bash
# Hard refresh
Ctrl + F5 (Windows)
Cmd + Shift + R (Mac)
```

**Check 2: Verify Image Exists**

```bash
# PowerShell
Test-Path "storage/app/public/properties/virtual-tours/test-panorama.jpg"
# Should return: True
```

**Check 3: Check Browser Console**

```
F12 → Console tab
Look for any errors related to Photo Sphere Viewer or image loading
```

**Check 4: Verify Property Data**

```bash
php artisan tinker
```

```php
$property = App\Models\Property::where('slug', 'beachfront-paradise-panglao-island')->first();
$property->has_virtual_tour; // Should be true
$property->virtual_tour_images; // Should show array with image path
```

---

## 📱 Testing with Your Own Phone Panorama

### To test with a real panorama from your smartphone:

**Step 1: Take a Panorama Photo**

-   **iPhone**: Camera app → Swipe to "PANO" mode → Take panorama
-   **Android**: Camera app → Select "Panorama" mode → Take panorama

**Step 2: Transfer to Your Computer**

-   Save the panorama to your computer
-   Note: Panoramas are usually very wide images (aspect ratio around 2:1)

**Step 3: Upload to Storage**

```bash
# Copy your panorama to:
storage/app/public/properties/virtual-tours/my-beach-panorama.jpg
```

**Step 4: Update the Property**

```bash
php artisan tinker
```

```php
$property = App\Models\Property::where('slug', 'beachfront-paradise-panglao-island')->first();
$property->virtual_tour_images = ['properties/virtual-tours/my-beach-panorama.jpg'];
$property->save();
```

**Step 5: Refresh Browser**

-   Hard refresh (Ctrl+F5)
-   View the property page
-   Click "360° Panorama" tab
-   Your panorama should now display!

---

## ✨ What Should Work:

### ✅ Expected Behavior:

-   Property detail page loads
-   Two tabs visible: "Photo Gallery" and "360° Panorama"
-   Clicking "Photo Gallery" shows 3 beachfront images with thumbnails
-   Clicking "360° Panorama" shows interactive panoramic viewer
-   Can drag to look around the panorama
-   Can scroll/pinch to zoom
-   Smooth tab switching

### ❌ What's NOT Working Yet:

If you still see "Failed to load 360° image":

1. The image path might be incorrect
2. CORS issues with external URLs (use local storage instead)
3. Image format is not equirectangular
4. Photo Sphere Viewer library not loaded properly

---

## 🎓 For Broker Testing

When testing as a broker creating/editing properties:

1. **Login as a broker**:

    - Email: `maria@geocasabohol.com`
    - Password: `password`

2. **Create/Edit a property**

3. **Enable Virtual Tour**:

    - Check the "Enable Virtual Tour" checkbox
    - Upload a panorama image to "Virtual Tour Images" section

4. **Save and View**:
    - Save the property
    - View it on the public property detail page
    - Verify both tabs appear

---

## 📊 Success Criteria

Your virtual tour is working correctly if:

-   ✅ Tabs appear when property has `has_virtual_tour = true`
-   ✅ Photo Gallery tab shows regular images
-   ✅360° Panorama tab shows interactive viewer
-   ✅ Can switch between tabs smoothly
-   ✅ Panorama loads without errors
-   ✅ Can drag, zoom, and explore the panorama
-   ✅ Works on both desktop and mobile

---

## 🚀 Next Steps

Once basic testing works:

1. Test with your own smartphone panoramas
2. Test uploading via broker interface
3. Add multiple panorama images (future enhancement)
4. Consider adding hotspots/markers (future enhancement)

---

**Current Status:** ✅ Test panorama image is ready in your storage!
**Test Property:** Beachfront Paradise - Panglao Island
**Image Location:** `storage/app/public/properties/virtual-tours/test-panorama.jpg`
