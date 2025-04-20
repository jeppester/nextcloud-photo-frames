#!/bin/env bash

# Check if a tag argument was provided
if [ -z "$1" ]; then
    echo "Error: Please provide a tag name as the first argument"
    echo "Usage: $0 <tag-name>"
    exit 1
fi

rm -rf build
mkdir -p build/photo_frames

cp -r appinfo build/photo_frames
cp -r css build/photo_frames
cp -r img build/photo_frames
cp -r js build/photo_frames
cp -r lib build/photo_frames
cp -r templates build/photo_frames

tar -czvf build/photo_frames.tar.gz -C build photo_frames
openssl dgst -sha512 -sign photo_frames.key build/photo_frames.tar.gz | openssl base64 > build/photo_frames.tar.gz.sha512

# Use the first argument ($1) as the tag for the release
gh release create "$1" build/photo_frames.tar.gz build/photo_frames.tar.gz.sha512
