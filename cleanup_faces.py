import os
import sys
import multiprocessing
from PIL import Image
import numpy as np

# --- CONFIGURATION ---
# ... (Configuration kept same)
DRY_RUN = False 
TOLERANCE = 0.62 # Slightly stricter than 0.65 because rotation increases false positive risk
TARGET_DIRS = ["assets/images/training"]
REFERENCE_IMAGE = "assets/images/bootcamp/FOUNDER.jpg"
VALID_EXTENSIONS = ('.jpg', '.jpeg', '.png', '.JPG', '.JPEG', '.PNG')
MAX_DIMENSION = 1600

# ... (Imports)

def process_file_fast(args):
    """
    Robust process file using resizing, rotation, and upsampling.
    """
    file_path, ref_encoding, tolerance, dry_run = args
    match_found = False
    error_occurred = False
    no_faces_found = True # Default to True until we find one

    try:
        import face_recognition
        
        # Helper detection function
        def check_image(img_np, upsample=1):
            encodings = face_recognition.face_encodings(img_np, num_jitters=1, model='small') 
            # Note: face_encodings handles location internally if not provided, but providing locations with upsampling helps
            # Let's trust standard face_encodings first which uses HOG
            
            # Explicit location finding for better upsampling control
            locations = face_recognition.face_locations(img_np, number_of_times_to_upsample=upsample)
            encodings = face_recognition.face_encodings(img_np, known_face_locations=locations)
            
            found_face = len(encodings) > 0
            is_match = False
            for encoding in encodings:
                matches = face_recognition.compare_faces([ref_encoding], encoding, tolerance=tolerance)
                if matches[0]:
                    is_match = True
                    break
            return found_face, is_match

        # 1. Load Original
        # We need PIL image for rotation
        original_pil = Image.open(file_path).convert('RGB')
        
        # Resize for speed - but keep reasonable quality for detection
        width, height = original_pil.size
        scale = 1.0
        if max(width, height) > MAX_DIMENSION:
            scale = MAX_DIMENSION / max(width, height)
            new_width = int(width * scale)
            new_height = int(height * scale)
            # Use original_pil for rotations to avoid resizing artifacts adding up
            working_pil = original_pil.resize((new_width, new_height), Image.Resampling.LANCZOS)
        else:
            working_pil = original_pil

        # Pass 1: Standard
        working_np = np.array(working_pil)
        has_faces, matched = check_image(working_np, upsample=1)
        if has_faces: no_faces_found = False
        
        if matched:
            match_found = True
        
        # Pass 2: Rotate -20 (Left tilt)
        if not match_found:
            rotated_pil = working_pil.rotate(-20, expand=False)
            has_faces_r, matched_r = check_image(np.array(rotated_pil))
            if has_faces_r: no_faces_found = False
            if matched_r: match_found = True

        # Pass 3: Rotate +20 (Right tilt)
        if not match_found:
            rotated_pil = working_pil.rotate(20, expand=False)
            has_faces_r, matched_r = check_image(np.array(rotated_pil))
            if has_faces_r: no_faces_found = False
            if matched_r: match_found = True

        # Pass 4: Stronger Upsample (Slower, only if needed and we haven't found matches yet)
        if not match_found:
             # Try upsampling on the non-rotated image
             has_faces_up, matched_up = check_image(working_np, upsample=2)
             if has_faces_up: no_faces_found = False
             if matched_up: match_found = True

        # Final Action
        if match_found and not dry_run:
            if os.path.exists(file_path):
                os.remove(file_path)

    except Exception:
        error_occurred = True
    
    return (file_path, match_found, error_occurred, no_faces_found)

def cleanup_founder_clones():
    # ... (Keep existing setup code)
    script_dir = os.path.dirname(os.path.abspath(__file__))
    ref_abs_path = os.path.join(script_dir, REFERENCE_IMAGE)
    
    if not os.path.exists(ref_abs_path):
        print(f"Error: Reference image NOT found at {ref_abs_path}")
        return

    print(f"Loading reference face from: {REFERENCE_IMAGE}")
    try:
        import face_recognition
        ref_image = face_recognition.load_image_file(ref_abs_path)
        ref_encodings = face_recognition.face_encodings(ref_image)
        
        if not ref_encodings:
            print("Error: No face detected in the reference image.")
            return
            
        ref_encoding = ref_encodings[0]
        print("Reference face encoded successfully.")
    except Exception as e:
        print(f"Failed to load reference image: {e}")
        return

    if DRY_RUN:
        print("\nDRY RUN ENABLED: No files will be deleted.")
    else:
        print("\nWARNING: DRY RUN DISABLED. Files matching the founder will be PERMANENTLY DELETED.")

    all_tasks = []
    print("\nScanning directories for files...")
    
    for target_dir in TARGET_DIRS:
        abs_target_dir = os.path.join(script_dir, target_dir)
        if not os.path.exists(abs_target_dir):
            print(f"Skipping {target_dir} (not found).")
            continue

        files = [f for f in os.listdir(abs_target_dir) if f.lower().endswith(VALID_EXTENSIONS)]
        for filename in files:
            file_path = os.path.join(abs_target_dir, filename)
            if os.path.abspath(file_path) == os.path.abspath(ref_abs_path):
                continue
            all_tasks.append((file_path, ref_encoding, TOLERANCE, DRY_RUN))

    total_files = len(all_tasks)
    print(f"Found {total_files} images. Starting ROBUST parallel scan (Rotation +/-20 deg, Upsample x2)...")

    deleted_count = 0
    checked_count = 0
    errors_count = 0
    no_faces_count = 0

    with multiprocessing.Pool() as pool:
        for result in pool.imap_unordered(process_file_fast, all_tasks):
            checked_count += 1
            file_path, match_found, error_occurred, no_faces = result
            filename = os.path.basename(file_path)

            status = f"[{checked_count}/{total_files}] Checked {filename}"
            print(f"\r{status[:75]:<75}", end="", flush=True)

            if error_occurred:
                errors_count += 1
            elif match_found:
                print(f"\nMatch found: {filename}")
                if not DRY_RUN:
                    print(f"    Deleted: {file_path}")
                else:
                    print(f"    [DRY RUN] Would delete: {file_path}")
                deleted_count += 1
            elif no_faces:
                no_faces_count += 1

    print(f"\n\n{'-'*30}")
    print(f"Scan Complete.")
    print(f"Total checked: {checked_count}")
    print(f"Matches found: {deleted_count}")
    print(f"No faces found: {no_faces_count}")
    print(f"Errors: {errors_count}")
    
    if not DRY_RUN:
        print(f"Successfully removed {deleted_count} duplicate faces.")

if __name__ == "__main__":
    multiprocessing.freeze_support()
    cleanup_founder_clones()
