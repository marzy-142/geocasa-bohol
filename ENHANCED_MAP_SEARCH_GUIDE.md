# Enhanced GIS Map Search - Setup Guide

## Overview

The GeoCasa Bohol application now features **precision-enhanced location search** with multiple fallback strategies and optional Google Maps integration for superior accuracy.

## Features

### ✨ Smart Search Capabilities

1. **Multi-Source Geocoding**

    - Primary: Google Places API (if configured)
    - Fallback: OpenStreetMap Nominatim
    - Multiple search strategies for maximum coverage

2. **Intelligent Search Strategies**

    - Exact match with full context
    - Broader Philippines-wide search (filtered to Bohol)
    - Partial name matching (e.g., "Cabantian" from "Cabantian Hills")
    - Municipality-specific searches
    - Auto-complete as you type (500ms debounce)

3. **Interactive Results**

    - Dropdown with top 5 results
    - Visual indicators for data source (Google/OSM)
    - Location type labels
    - Distance-based relevance sorting

4. **Enhanced User Experience**
    - Real-time search as you type
    - Clear error messages with suggestions
    - Automatic boundary checking for Bohol
    - Fallback to manual map clicking

## Setup Instructions

### Option 1: Free Mode (OpenStreetMap Only)

**No additional setup required!** The map will work immediately with OpenStreetMap data.

**Limitations:**

-   May not find small localities or informal place names
-   Less precise for subdivisions and hills
-   Limited coverage for newly developed areas

**Best for:**

-   Development/testing
-   Budget-conscious deployments
-   Locations with well-established OSM data

### Option 2: Premium Mode (Google Maps + OpenStreetMap)

For **maximum precision** (recommended for production):

#### Step 1: Get Google Maps API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable the **Geocoding API**:
    - Navigate to "APIs & Services" → "Library"
    - Search for "Geocoding API"
    - Click "Enable"
4. Create credentials:
    - Go to "APIs & Services" → "Credentials"
    - Click "Create Credentials" → "API Key"
    - Copy your API key
5. **Secure your API key**:
    - Click "Edit API key"
    - Under "API restrictions", select "Restrict key"
    - Choose "Geocoding API"
    - Under "Application restrictions", add your domain

#### Step 2: Configure Your Application

1. Open your `.env` file
2. Add the Google Maps API key:

```bash
GOOGLE_MAPS_API_KEY=your_api_key_here
```

3. Restart your application:

```bash
php artisan config:clear
php artisan cache:clear
npm run build  # or npm run dev
```

#### Step 3: Verify Setup

1. Navigate to Property Creation page
2. Enable GIS Mapping
3. Search for "Cabantian Hills, Guindulman"
4. You should see results with "📍 Google Maps" indicator

## Usage Examples

### For Precise Locations

**Best Format:**

```
Cabantian Hills, Guindulman, Bohol
```

**Also Works:**

```
Cabantian Hills
Cabantian, Guindulman
Barangay Cabantian, Guindulman
```

### For Common Places

```
Panglao
Alona Beach
Chocolate Hills
Tagbilaran City Hall
```

### For Barangays

```
Poblacion, Tagbilaran
Tawala, Panglao
Dao, Dauis
```

## Pricing (Google Maps)

Google provides **$200 free credit per month**, which covers:

-   ~40,000 geocoding requests/month
-   More than enough for typical real estate platforms

**Beyond free tier:**

-   $5 per 1,000 requests
-   Monitor usage in Google Cloud Console

## Troubleshooting

### "Location not found" Error

**Try these strategies:**

1. **Be more specific:**

    - ❌ `Hills`
    - ✅ `Cabantian Hills, Guindulman`

2. **Use municipality names:**

    - ❌ `Seaside property`
    - ✅ `Panglao beachfront`

3. **Try barangay names:**

    - ❌ `Near chocolate hills`
    - ✅ `Carmen, Bohol`

4. **Manual fallback:**
    - Click directly on the map where the property is located
    - Fine-tune the marker position

### Google API Not Working

**Checklist:**

1. API key is correctly set in `.env`
2. Geocoding API is enabled in Google Cloud
3. Config cache is cleared: `php artisan config:clear`
4. API key has no domain restrictions (for testing)
5. Check browser console for API errors

### Results Outside Bohol

The system automatically filters results to Bohol boundaries:

-   North: 10.2°
-   South: 9.3°
-   East: 124.5°
-   West: 123.5°

If you see a warning, the location is outside these bounds.

## Technical Details

### Search Flow

```
User types → Debounce 500ms → Multi-source search
                                    ↓
                    ┌───────────────┴───────────────┐
                    ↓                               ↓
            Google Places API              OpenStreetMap
            (if configured)                  Nominatim
                    ↓                               ↓
                    └───────────────┬───────────────┘
                                    ↓
                        Filter to Bohol bounds
                                    ↓
                        Sort by relevance
                                    ↓
                        Display top 5 results
```

### Data Sources Compared

| Feature              | Google Maps  | OpenStreetMap |
| -------------------- | ------------ | ------------- |
| Small localities     | ✅ Excellent | ⚠️ Limited    |
| Subdivisions         | ✅ Yes       | ❌ Rare       |
| Informal names       | ✅ Often     | ❌ Rarely     |
| Municipality/City    | ✅ Yes       | ✅ Yes        |
| Barangays            | ✅ Yes       | ✅ Usually    |
| Cost                 | $5/1000      | Free          |
| Philippines coverage | ✅ Excellent | ⚠️ Good       |

## Maintenance

### Monitoring Usage (Google)

1. Go to Google Cloud Console
2. Navigate to "APIs & Services" → "Dashboard"
3. Click "Geocoding API"
4. View quota and usage graphs

### Best Practices

1. **Cache common searches** (future enhancement)
2. **Educate users** on effective search terms
3. **Monitor API costs** if using Google
4. **Keep municipality list updated** in Property model
5. **Test with real user queries** to refine search

## Future Enhancements

-   [ ] Cache geocoding results to reduce API calls
-   [ ] Add "Did you mean?" suggestions
-   [ ] Support for nearby landmarks search
-   [ ] Polygon boundary display for barangays
-   [ ] Bulk location import from CSV
-   [ ] Historical search suggestions

## Support

For issues or questions:

1. Check browser console for errors
2. Verify API key configuration
3. Test with simple queries first (e.g., "Tagbilaran")
4. Check Google Cloud Console for quota/errors

---

**Last Updated:** November 3, 2025
**Version:** 2.0 - Enhanced Multi-Source Search
