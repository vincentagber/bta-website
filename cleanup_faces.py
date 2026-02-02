import os
import sys
import shutil

# --- CONFIGURATION ---
DRY_RUN = True  # Set to False to actually delete files
TOLERANCE = 0.5 # Lower = stricter, Higher = looser (0.6 is default)
TARGET_DIRS = ["assets/images/bootcamp", "assets/images/training"]
REFERENCE_IMAGE = "assets/images/bootcamp/FOUNDER.jpg"
VALID_EXTENSIONS = ('.jpg', '.jpeg', '.png', '.JPG', '.JPEG', '.PNG')

try:
    import face_recognition
except ImportError:
    print("\u2716 Error: 'face_recognition' library not found.")
    print("--------------------------------------------------")
    print("To fix this, please install the library using:")
    print("python3 -m pip install face_recognition --break-system-packages")
    print("--------------------------------------------------")
    sys.exit(1)

def cleanup_founder_clones():
    # Get absolute path for reference to avoid relative path issues
    script_dir = os.path.dirname(os.path.abspath(__file__))
    ref_abs_path = os.path.join(script_dir, REFERENCE_IMAGE)
    
    if not os.path.exists(ref_abs_path):
        print(f"\u2716 Error: Reference image NOT found at {ref_abs_path}")
        return

    print(f"\ud83d\udd0d Loading reference face from: {REFERENCE_IMAGE}")
    try:
        ref_image = face_recognition.load_image_file(ref_abs_path)
        ref_encodings = face_recognition.face_encodings(ref_image)
        
        if not ref_encodings:
            print("Error: No face detected in the reference image. Please use a clear portrait.")
            return
            
        ref_encoding = ref_encodings[0]
        print("\u2705 Reference face encoded successfully.")
    except Exception as e:
        print(f"Failed to load reference image: {e}")
        return

    if DRY_RUN:
        print("\n\u26a0\ufe0f  DRY RUN ENABLED: No files will be deleted.")
    else:
        print("\n\ud83d\udea8 WARNING: DRY RUN DISABLED. Files matching the founder will be PERMANENTLY DELETED.")

    deleted_count = 0
    checked_count = 0
    errors_count = 0

    print("\nStarting scan...")

    for target_dir in TARGET_DIRS:
        abs_target_dir = os.path.join(script_dir, target_dir)
        if not os.path.exists(abs_target_dir):
            print(f"Skipping {target_dir} (not found).")
            continue

        print(f"\nScanning: {target_dir}")
        
        # Get list of files to process
        files = [f for f in os.listdir(abs_target_dir) if f.lower().endswith(VALID_EXTENSIONS)]
        total_files = len(files)

        for i, filename in enumerate(files):
            file_path = os.path.join(abs_target_dir, filename)
            
            # Skip the reference file itself
            if os.path.abspath(file_path) == os.path.abspath(ref_abs_path):
                continue

            checked_count += 1
            # Clear line and print status
            status = f"[{i+1}/{total_files}] Checking {filename}"
            # Pad with spaces to clear previous longer lines
            print(f"\r{status[:75]:<75}", end="", flush=True)

            try:
                # Optimized: We use basic detection first
                current_image = face_recognition.load_image_file(file_path)
                current_encodings = face_recognition.face_encodings(current_image)

                for encoding in current_encodings:
                    matches = face_recognition.compare_faces([ref_encoding], encoding, tolerance=TOLERANCE)
                    
                    if matches[0]:
                        print(f"\n\ud83c\udfaf Match found: {filename}")
                        if not DRY_RUN:
                            os.remove(file_path)
                            print(f"    \u2716 Deleted: {file_path}")
                        else:
                            print(f"    [DRY RUN] Would delete: {file_path}")
                        deleted_count += 1
                        break # Move to next file
            except Exception:
                errors_count += 1
                continue

    print(f"\n\n{'-'*30}")
    print(f"Scan Complete.")
    print(f"Total checked: {checked_count}")
    print(f"Matches found: {deleted_count}")
    print(f"Errors/Skipped: {errors_count}")
    
    if DRY_RUN and deleted_count > 0:
        print("\nTo actually delete these files, set DRY_RUN=False in the script.")
    elif not DRY_RUN:
        print(f"\u2705 Successfully removed {deleted_count} duplicate faces.")

if __name__ == "__main__":
    cleanup_founder_clones()
