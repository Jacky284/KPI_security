import React, { useRef, useState, forwardRef, useImperativeHandle, useEffect } from 'react';
import SignatureCanvas from 'react-signature-canvas';
import { Button } from '@/components/ui/button';

interface SignaturePadProps {
  onConfirm: (base64Data: string) => void;
  label: string;
  savedSignatureUrl?: string | null;
  autoFill?: boolean;
}
export interface SignaturePadRef {
  clear: () => void;
}

export const SignaturePad = forwardRef<SignaturePadRef, SignaturePadProps>(({ onConfirm, label, savedSignatureUrl, autoFill }, ref) => {
  const sigRef = useRef<SignatureCanvas>(null);
  const [hasSignature, setHasSignature] = useState(false);
  const [isSaved, setIsSaved] = useState(false);
  const [isUsingSaved, setIsUsingSaved] = useState(false);

  const clear = () => {
    sigRef.current?.clear();
    setHasSignature(false);
    setIsSaved(false);
    setIsUsingSaved(false);
  };

  useImperativeHandle(ref, () => ({
    clear
  }));

  useEffect(() => {
    if (autoFill && savedSignatureUrl && sigRef.current) {
      // Need a small timeout to ensure canvas is ready
      setTimeout(() => {
        sigRef.current?.fromDataURL('/storage/' + savedSignatureUrl, { ratio: 1, width: 300, height: 120 });
        setHasSignature(true);
        setIsSaved(true);
        setIsUsingSaved(true);
      }, 100);
    }
  }, [autoFill, savedSignatureUrl]);

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
      onConfirm(dataUrl, isUsingSaved);
    }
  };

  return (
    <div className="flex flex-col gap-2">
      <div className="flex flex-col gap-2 border p-3 rounded-lg bg-card shadow-sm">
        <span className="text-sm font-semibold text-foreground">{label}</span>
        <div 
          className="border bg-white rounded-md overflow-hidden cursor-crosshair flex justify-center mx-auto w-full max-w-[300px]"
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
              className: 'sigCanvas touch-none w-full h-auto'
            }}
            onEnd={handleEnd}
            onBegin={() => {
              setIsSaved(false);
              setIsUsingSaved(false);
            }}
          />
        </div>
        <div className="flex justify-between items-center mt-1">
          <span className="text-[10px] text-muted-foreground italic">Sign inside the box</span>
          <div className="flex gap-2">
            {savedSignatureUrl && !autoFill && (
              <Button 
                variant="default" 
                size="sm" 
                type="button" 
                className="bg-blue-600 hover:bg-blue-700 text-white text-[10px]"
                onClick={() => {
                  sigRef.current?.fromDataURL('/storage/' + savedSignatureUrl, { ratio: 1, width: 300, height: 120 });
                  setHasSignature(true);
                  setIsUsingSaved(true);
                }}
              >
                Tanda Tangani
              </Button>
            )}
            <Button variant="outline" size="sm" type="button" onClick={clear}>
              Clear
            </Button>
          </div>
        </div>
      </div>
      <Button
        size="sm"
        className={`w-full font-bold uppercase text-[10px] ${autoFill && isSaved ? 'bg-green-600 text-white disabled:opacity-100 disabled:bg-green-600 cursor-default' : 'bg-primary'}`}
        onClick={handleSimpan}
        disabled={!hasSignature || (autoFill && isSaved)}
      >
        {autoFill && isSaved ? '✓ Tanda Tangan Aktif' : 'Simpan Tanda Tangan'}
      </Button>
    </div>
  );
});
