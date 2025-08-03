#!/bin/bash

# Quick fix script for Simple Knowledge Base directory permissions
# Run this script if you're having database connection issues

echo "🔧 Simple Knowledge Base - Quick Fix Script"
echo "============================================="

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
echo "📁 Working directory: $SCRIPT_DIR"

# Create directories
echo ""
echo "📁 Creating directories..."

if [ ! -d "$SCRIPT_DIR/data" ]; then
    mkdir -p "$SCRIPT_DIR/data"
    echo "✅ Created: data/"
else
    echo "✅ Exists: data/"
fi

if [ ! -d "$SCRIPT_DIR/uploads" ]; then
    mkdir -p "$SCRIPT_DIR/uploads"
    echo "✅ Created: uploads/"
else
    echo "✅ Exists: uploads/"
fi

# Set permissions
echo ""
echo "🔐 Setting permissions..."

chmod 755 "$SCRIPT_DIR/data"
echo "✅ Set permissions for data/ to 755"

chmod 755 "$SCRIPT_DIR/uploads"
echo "✅ Set permissions for uploads/ to 755"

# Check if we need to be more permissive
if [ ! -w "$SCRIPT_DIR/data" ] || [ ! -w "$SCRIPT_DIR/uploads" ]; then
    echo ""
    echo "⚠️  Still not writable, trying more permissive settings..."
    chmod 777 "$SCRIPT_DIR/data"
    chmod 777 "$SCRIPT_DIR/uploads"
    echo "✅ Set permissions to 777 (less secure but should work)"
fi

# Check web server user
echo ""
echo "👤 Web server user detection..."

if command -v apache2 &> /dev/null; then
    echo "🌐 Apache detected"
    WEB_USER="www-data"
elif command -v nginx &> /dev/null; then
    echo "🌐 Nginx detected"
    WEB_USER="www-data"
elif command -v httpd &> /dev/null; then
    echo "🌐 HTTP daemon detected"
    WEB_USER="apache"
else
    echo "🌐 Web server not detected, using generic approach"
    WEB_USER=""
fi

if [ ! -z "$WEB_USER" ] && id "$WEB_USER" &>/dev/null; then
    echo "👤 Web server user: $WEB_USER"
    echo "💡 You might want to run:"
    echo "   sudo chown -R $WEB_USER:$WEB_USER $SCRIPT_DIR"
    echo "   (This will give the web server ownership of all files)"
fi

# Test permissions
echo ""
echo "🧪 Testing permissions..."

if [ -w "$SCRIPT_DIR/data" ]; then
    echo "✅ data/ directory is writable"
    
    # Test file creation
    if touch "$SCRIPT_DIR/data/test_file" 2>/dev/null; then
        echo "✅ Can create files in data/"
        rm -f "$SCRIPT_DIR/data/test_file"
    else
        echo "❌ Cannot create files in data/"
    fi
else
    echo "❌ data/ directory is not writable"
fi

if [ -w "$SCRIPT_DIR/uploads" ]; then
    echo "✅ uploads/ directory is writable"
    
    # Test file creation
    if touch "$SCRIPT_DIR/uploads/test_file" 2>/dev/null; then
        echo "✅ Can create files in uploads/"
        rm -f "$SCRIPT_DIR/uploads/test_file"
    else
        echo "❌ Cannot create files in uploads/"
    fi
else
    echo "❌ uploads/ directory is not writable"
fi

echo ""
echo "🎉 Quick fix complete!"
echo ""
echo "Next steps:"
echo "1. Visit diagnostics.php in your browser to verify everything is working"
echo "2. Run install.php to set up the database"
echo "3. Start using your knowledge base!"
echo ""
echo "If you still have issues:"
echo "- Try running this script with sudo"
echo "- Check your web server error logs"
echo "- Visit diagnostics.php for detailed troubleshooting"
