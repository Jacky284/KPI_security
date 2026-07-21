import React, { useRef, useState } from 'react';
import SignatureCanvas from 'react-signature-canvas';
import { Button } from '@/components/ui/button';

interface SignaturePadProps {
  onConfirm: (base64Data: string) => void;
  label: string;
}

export function SignaturePad({ onConfirm, label }: SignaturePadProps) {
  const sigRef = useRef<SignatureCanvas>(null);
  const [hasSignature, setHasSignature] = useState(false);

  const clear = () => {
    sigRef.current?.clear();
    setHasSignature(false);
  };

  const handleEnd = () => {
    if (sigRef.current && !sigRef.current.isEmpty()) {
      setHasSignature(true);
    } else {
      setHasSignature(false);
    }
  };

  const handleSimpan = () => {
    if (sigRef.current && !sigRef.current.isEmpty()) {
      const dataUrl = sigRef.current.toDataURL('image/png');
      onConfirm(dataUrl);
    }
  };

  return (
    <div className="flex flex-col gap-2">
      <div className="flex flex-col gap-2 border p-3 rounded-lg bg-card shadow-sm">
        <span className="text-sm font-semibold text-foreground">{label}</span>
        <div 
          className="border bg-white rounded-md overflow-hidden cursor-crosshair"
          onMouseUp={handleEnd}
          onMouseOut={handleEnd}
          onTouchEnd={handleEnd}
        >
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
      <Button
        size="sm"
        className="w-full bg-primary font-bold uppercase text-[10px]"
        onClick={handleSimpan}
        disabled={!hasSignature}
      >
        Simpan Tanda Tangan
      </Button>
    </div>
  );
}
