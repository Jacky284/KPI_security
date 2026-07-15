import React, { useRef } from 'react';
import SignatureCanvas from 'react-signature-canvas';
import { Button } from '@/components/ui/button';

interface SignaturePadProps {
  onSave: (base64Data: string) => void;
  label: string;
}

export function SignaturePad({ onSave, label }: SignaturePadProps) {
  const sigRef = useRef<SignatureCanvas>(null);

  const clear = () => {
    sigRef.current?.clear();
    onSave('');
  };

  const handleEnd = () => {
    if (sigRef.current) {
      if (sigRef.current.isEmpty()) {
        onSave('');
      } else {
        const dataUrl = sigRef.current.getTrimmedCanvas().toDataURL('image/png');
        onSave(dataUrl);
      }
    }
  };

  return (
    <div className="flex flex-col gap-2 border p-3 rounded-lg bg-card shadow-sm">
      <span className="text-sm font-semibold text-foreground">{label}</span>
      <div className="border bg-white rounded-md overflow-hidden cursor-crosshair">
        <SignatureCanvas
          ref={sigRef}
          penColor="black"
          canvasProps={{
            width: 300,
            height: 120,
            className: 'sigCanvas w-full h-[120px]'
          }}
          onEnd={handleEnd}
        />
      </div>
      <div className="flex justify-between items-center mt-1">
        <span className="text-[10px] text-muted-foreground italic">Sign inside the box</span>
        <Button variant="outline" size="sm" type="button" onClick={clear}>
          Clear
        </Button>
      </div>
    </div>
  );
}
