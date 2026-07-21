path = r'F:\Vestra website\frontend\app\globals.css'

css = r'''@import "tailwindcss";
@import "tw-animate-css";
@import "shadcn/tailwind.css";

@custom-variant dark (&:is(.dark *));

@theme inline {
  --color-background: var(--background);
  --color-foreground: var(--foreground);
}

:root {
  --background: #ffffff;
  --foreground: #334155;
}
'''

with open(path, 'w', encoding='utf-8') as f:
    f.write(css)
