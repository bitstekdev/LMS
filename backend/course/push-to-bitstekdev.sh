#!/bin/bash
set -e

# === Config ===
REPO_A_URL="https://github.com/bitstekdev/LMS.git"   # 🔹 Repo A URL
REPO_A_REMOTE="repo-a"                               # 🔹 Local alias for Repo A
REPO_A_BRANCH="abbasmashaddy72"                      # 🔹 Branch inside Repo A
TARGET_SUBFOLDER="backend/course"                    # 🔹 Subfolder in Repo A
SOURCE_BRANCH="main"                                 # 🔹 Branch from Repo B

echo "🔧 Syncing Repo B -> Repo A"

# === Step 1: Ensure Repo A is added as remote ===
if ! git remote | grep -q "$REPO_A_REMOTE"; then
  echo "🔗 Adding Repo A remote..."
  git remote add $REPO_A_REMOTE $REPO_A_URL
else
  echo "✅ Repo A remote already exists."
fi

# === Step 2: Fetch Repo A branches ===
echo "⬇️ Fetching Repo A branches..."
git fetch $REPO_A_REMOTE

# === Step 3: Check if branch exists in Repo A ===
if git ls-remote --exit-code --heads $REPO_A_REMOTE $REPO_A_BRANCH >/dev/null 2>&1; then
  echo "✅ Branch '$REPO_A_BRANCH' exists in Repo A."
else
  echo "🌱 Branch '$REPO_A_BRANCH' does not exist. Will create it."
fi

# === Step 4: Create temp clone with rewritten paths ===
TMP_DIR=$(mktemp -d)
echo "📂 Creating temp clone in $TMP_DIR"
git clone . "$TMP_DIR/repo-b-tmp"
cd "$TMP_DIR/repo-b-tmp"

# Checkout correct branch
git checkout $SOURCE_BRANCH

# Rewrite history so all files go under the subfolder
echo "📦 Rewriting history into subfolder '$TARGET_SUBFOLDER/'..."
git filter-repo --to-subdirectory-filter "$TARGET_SUBFOLDER" --force

# === Step 5: Push rewritten history into Repo A branch ===
echo "⬆️ Pushing Repo B into Repo A branch '$REPO_A_BRANCH'..."
git remote add $REPO_A_REMOTE $REPO_A_URL || true
git push $REPO_A_REMOTE HEAD:$REPO_A_BRANCH --force

# === Step 6: Cleanup ===
cd -
rm -rf "$TMP_DIR"

echo "✅ Done!"
echo "👉 Repo B has been pushed into Repo A branch '$REPO_A_BRANCH' under folder '$TARGET_SUBFOLDER/'"
