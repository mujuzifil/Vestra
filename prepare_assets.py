from PIL import Image, ImageFilter
from rembg import remove
import os
import numpy as np

# Product images to process
products = [
    "Heavy Duty Detergent.jpeg",
    "Silk Care.jpeg",
    "EcoSuit Cleaner.jpeg",
    "Pro Finish Garment spray.jpeg",
    "Stain Pro.jpeg",
    "Wool & Delicate Fabric Wash.jpeg"
]

# Target square canvas size (large enough for high resolution)
CANVAS_SIZE = 1200


def clean_edges(img, white_threshold=240, dark_threshold=25, edge_feather=2):
    """
    Remove white/dark matte remnants around transparent edges while preserving
    anti-aliasing and natural shadows.
    """
    rgba = img.convert("RGBA")
    pixels = np.array(rgba)
    r, g, b, a = pixels[:, :, 0], pixels[:, :, 1], pixels[:, :, 2], pixels[:, :, 3]

    # Create a mask for visible pixels
    visible = a > 0

    # Detect edge pixels: visible pixels that are near a fully transparent pixel
    # Use dilation/erosion on alpha channel
    alpha_bool = (a > 20).astype(np.uint8)
    from scipy import ndimage
    eroded = ndimage.binary_erosion(alpha_bool, iterations=1)
    edge_mask = visible & ~eroded

    # Near-white pixels on edges become transparent
    brightness = np.maximum.reduce([r, g, b])
    near_white = brightness > white_threshold
    # Near-black pixels on edges become transparent
    darkness = np.minimum.reduce([r, g, b])
    near_black = darkness < dark_threshold

    # Reduce alpha for problematic edge pixels
    problem_mask = edge_mask & (near_white | near_black)
    # Fade them out based on how extreme the color is
    fade = np.ones_like(a, dtype=np.float32)
    white_strength = (brightness - white_threshold) / (255 - white_threshold)
    black_strength = (dark_threshold - darkness) / dark_threshold
    strength = np.maximum(white_strength, black_strength)
    strength = np.clip(strength, 0, 1)
    fade[problem_mask] = 1.0 - strength[problem_mask]

    a_clean = (a.astype(np.float32) * fade).astype(np.uint8)
    pixels[:, :, 3] = a_clean

    cleaned = Image.fromarray(pixels, "RGBA")

    # Slight Gaussian blur on the alpha channel only to smooth edges
    alpha = cleaned.split()[-1]
    alpha = alpha.filter(ImageFilter.GaussianBlur(radius=edge_feather))
    cleaned.putalpha(alpha)

    return cleaned


def center_on_canvas(img, canvas_size):
    """Center an RGBA image on a transparent square canvas, preserving aspect ratio."""
    bbox = img.getbbox()
    if bbox:
        img = img.crop(bbox)

    max_dim = int(canvas_size * 0.92)  # 4% padding
    w, h = img.size
    scale = min(max_dim / w, max_dim / h, 1.0)
    new_w = int(w * scale)
    new_h = int(h * scale)

    if new_w != w or new_h != h:
        img = img.resize((new_w, new_h), Image.LANCZOS)

    canvas = Image.new("RGBA", (canvas_size, canvas_size), (255, 255, 255, 0))
    x = (canvas_size - new_w) // 2
    y = (canvas_size - new_h) // 2
    canvas.paste(img, (x, y), img)
    return canvas


def process_image(input_path, output_path, canvas_size=CANVAS_SIZE):
    print(f"Processing {input_path}...")
    with Image.open(input_path).convert("RGBA") as img:
        # Remove background using rembg
        output = remove(img)
        # Clean edge artifacts
        output = clean_edges(output)
        # Center on canvas
        output = center_on_canvas(output, canvas_size)
        # Save with optimization
        output.save(output_path, "PNG", optimize=True)
        print(f"Saved {output_path}")

        # Generate responsive srcset versions
        base, ext = os.path.splitext(output_path)
        for size in [400, 800]:
            scaled = output.resize((size, size), Image.LANCZOS)
            scaled_path = f"{base}-{size}w.png"
            scaled.save(scaled_path, "PNG", optimize=True)
            print(f"Saved {scaled_path}")


def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))

    # Process product images
    for filename in products:
        input_path = os.path.join(base_dir, filename)
        name, _ = os.path.splitext(filename)
        output_path = os.path.join(base_dir, f"{name}.png")
        if os.path.exists(input_path):
            process_image(input_path, output_path)
        else:
            print(f"Warning: {input_path} not found")

    # Process logo
    logo_input = os.path.join(base_dir, "Vestra logo.jpeg")
    logo_output = os.path.join(base_dir, "Vestra logo.png")
    if os.path.exists(logo_input):
        print(f"Processing logo...")
        with Image.open(logo_input).convert("RGBA") as img:
            output = remove(img)
            output = clean_edges(output, white_threshold=250, dark_threshold=15)
            bbox = output.getbbox()
            if bbox:
                output = output.crop(bbox)
            output.save(logo_output, "PNG", optimize=True)
            print(f"Saved {logo_output}")
    else:
        print(f"Warning: {logo_input} not found")


if __name__ == "__main__":
    main()
