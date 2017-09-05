// gifWriter.js
// ============

// (c) Dean McNamee <dean@gmail.com>, 2013.
//
// https://github.com/deanm/omggif
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to
// deal in the Software without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
// sell copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
// IN THE SOFTWARE.
//
// omggif is a JavaScript implementation of a GIF 89a encoder and decoder,
// including animation and compression.  It does not rely on any specific
// underlying system, so should run in the browser, Node, or Plask.

define([
    'core/utils'
], function(utils) {
    return function gifWriter(buf, width, height, goOpts) {
        var p = 0;

        goOpts = goOpts === undefined ? {} : goOpts;
        var loopCount = goOpts.loop === undefined ? null : goOpts.loop;
        var globalPalette = goOpts.palette === undefined ? null : goOpts.palette;

        if (width <= 0 || height <= 0 || width > 65535 || height > 65535)
            throw "Width/Height invalid.";

        function checkPaletteAndNumColors(palette) {
            var numColors = palette.length;

            if (numColors < 2 || numColors > 256 || numColors & (numColors - 1))
                throw "Invalid code/color length, must be power of 2 and 2 .. 256.";
            return numColors;
        }

        // - Header.
        buf[p++] = 0x47;
        buf[p++] = 0x49;
        buf[p++] = 0x46; // GIF
        buf[p++] = 0x38;
        buf[p++] = 0x39;
        buf[p++] = 0x61; // 89a

        // Handling of Global Color Table (palette) and background index.
        var gpNumColorsPow2 = 0;
        var background = 0;

        // - Logical Screen Descriptor.
        // NOTE(deanm): w/h apparently ignored by implementations, but set anyway.
        buf[p++] = width & 0xFF;
        buf[p++] = width >> 8 & 0xFF;
        buf[p++] = height & 0xFF;
        buf[p++] = height >> 8 & 0xFF;
        // NOTE: Indicates 0-bpp original color resolution (unused?).
        buf[p++] = (globalPalette !== null ? 0x80 : 0) | gpNumColorsPow2; // NOTE: No sort flag (unused?).
        buf[p++] = background; // Background Color Index.
        buf[p++] = 0; // Pixel aspect ratio (unused?).

        if (loopCount !== null) {   // Netscape block for looping.
            if (loopCount < 0 || loopCount > 65535)
                throw "Loop count invalid.";

            // Extension code, label, and length.
            buf[p++] = 0x21;
            buf[p++] = 0xff;
            buf[p++] = 0x0b;
            // NETSCAPE2.0
            buf[p++] = 0x4e;
            buf[p++] = 0x45;
            buf[p++] = 0x54;
            buf[p++] = 0x53;
            buf[p++] = 0x43;
            buf[p++] = 0x41;
            buf[p++] = 0x50;
            buf[p++] = 0x45;
            buf[p++] = 0x32;
            buf[p++] = 0x2e;
            buf[p++] = 0x30;
            // Sub-block
            buf[p++] = 0x03;
            buf[p++] = 0x01;
            buf[p++] = loopCount & 0xff;
            buf[p++] = loopCount >> 8 & 0xff;
            buf[p++] = 0x00; // Terminator.
        }

        var ended = false;

        this.addFrame = function(x, y, w, h, indexedPixels, opts) {
            if (ended === true) {
                --p;
                ended = false;
            }

            opts = opts === undefined ? {} : opts;

            // TODO(deanm): Bounds check x, y.  Do they need to be within the virtual
            // canvas width/height, I imagine?
            if (x < 0 || y < 0 || x > 65535 || y > 65535)
                throw "x/y invalid.";

            if (w <= 0 || h <= 0 || w > 65535 || h > 65535)
                throw "Width/Height invalid.";

            if (indexedPixels.length < w * h)
                throw "Not enough pixels for the frame size.";

            var usingLocalPalette = true;
            var palette = opts.palette;
            if (palette === undefined || palette === null) {
                usingLocalPalette = false;
                palette = globalPalette;
            }

            if (palette === undefined || palette === null)
                throw "Must supply either a local or global palette.";

            var numColors = checkPaletteAndNumColors(palette);

            // Compute the min_code_size (power of 2), destroying num_colors.
            var minCodeSize = 0;
            while (numColors >>= 1)
                ++minCodeSize;
            numColors = 1 << minCodeSize;   // Now we can easily get it back.

            var delay = opts.delay === undefined ? 0 : opts.delay;

            // From the spec:
            //     0 -   No disposal specified. The decoder is
            //           not required to take any action.
            //     1 -   Do not dispose. The graphic is to be left
            //           in place.
            //     2 -   Restore to background color. The area used by the
            //           graphic must be restored to the background color.
            //     3 -   Restore to previous. The decoder is required to
            //           restore the area overwritten by the graphic with
            //           what was there prior to rendering the graphic.
            //  4-7 -    To be defined.
            // NOTE(deanm): Dispose background doesn't really work, apparently most
            // browsers ignore the background palette index and clear to transparency.
            var disposal = opts.disposal === undefined ? 0 : opts.disposal;
            if (disposal < 0 || disposal > 3)   // 4-7 is reserved
                throw "Disposal out of range.";

            var useTransparency = false;
            var transparentIndex = 0;
            if (opts.transparent !== undefined && opts.transparent !== null) {
                useTransparency = true;
                transparentIndex = opts.transparent;
                if (transparentIndex < 0 || transparentIndex >= numColors)
                    throw "Transparent color index.";
            }

            if (disposal !== 0 || useTransparency || delay !== 0) {
                // - Graphics Control Extension
                buf[p++] = 0x21;
                buf[p++] = 0xf9; // Extension / Label.
                buf[p++] = 4; // Byte size.

                buf[p++] = disposal << 2 | (useTransparency === true ? 1 : 0);
                buf[p++] = delay & 0xff;
                buf[p++] = delay >> 8 & 0xff;
                buf[p++] = transparentIndex; // Transparent color index.
                buf[p++] = 0; // Block Terminator.
            }

            // - Image Descriptor
            buf[p++] = 0x2c; // Image Seperator.
            buf[p++] = x & 0xff;
            buf[p++] = x >> 8 & 0xff; // Left.
            buf[p++] = y & 0xff;
            buf[p++] = y >> 8 & 0xff; // Top.
            buf[p++] = w & 0xff;
            buf[p++] = w >> 8 & 0xff;
            buf[p++] = h & 0xff;
            buf[p++] = h >> 8 & 0xff;
            // NOTE: No sort flag (unused?).
            // TODO(deanm): Support interlace.
            buf[p++] = usingLocalPalette === true ? (0x80 | (minCodeSize - 1)) : 0;

            // - Local Color Table
            if (usingLocalPalette === true) {
                for (var i = 0, il = palette.length; i < il; ++i) {
                    var rgb = palette[i];
                    buf[p++] = rgb >> 16 & 0xff;
                    buf[p++] = rgb >> 8 & 0xff;
                    buf[p++] = rgb & 0xff;
                }
            }

            p = GifWriterOutputLZWCodeStream(buf, p, minCodeSize < 2 ? 2 : minCodeSize, indexedPixels);
        };

        this.end = function() {
            if (ended === false) {
                buf[p++] = 0x3B;    // Trailer.
                ended = true;
            }
            return p;
        };

        // Main compression routine, palette indexes -> LZW code stream.
        // |index_stream| must have at least one entry.
        function GifWriterOutputLZWCodeStream(buf, p, minCodeSize, indexStream) {
            buf[p++] = minCodeSize;
            var curSubBlock = p++; // Pointing at the length field.

            var clearCode = 1 << minCodeSize;
            var codeMask = clearCode - 1;
            var eoiCode = clearCode + 1;
            var nextCode = eoiCode + 1;

            var curCodeSize = minCodeSize + 1; // Number of bits per code.
            var curShift = 0;
            // We have at most 12-bit codes, so we should have to hold a max of 19
            // bits here (and then we would write out).
            var cur = 0;

            function emitBytesToBuffer(bitBlockSize) {
                while (curShift >= bitBlockSize) {
                    buf[p++] = cur & 0xFF;
                    cur >>= 8;
                    curShift -= 8;
                    if (p === curSubBlock + 256) {
                        buf[curSubBlock] = 255;
                        curSubBlock = p++;
                    }
                }
            }

            function emitCode(c) {
                cur |= c << curShift;
                curShift += curCodeSize;
                emitBytesToBuffer(8);
            }

            // I am not an expert on the topic, and I don't want to write a thesis.
            // However, it is good to outline here the basic algorithm and the few data
            // structures and optimizations here that make this implementation fast.
            // The basic idea behind LZW is to build a table of previously seen runs
            // addressed by a short id (herein called output code).  All data is
            // referenced by a code, which represents one or more values from the
            // original input stream.  All input bytes can be referenced as the same
            // value as an output code.  So if you didn't want any compression, you
            // could more or less just output the original bytes as codes (there are
            // some details to this, but it is the idea).  In order to achieve
            // compression, values greater then the input range (codes can be up to
            // 12-bit while input only 8-bit) represent a sequence of previously seen
            // inputs.  The decompressor is able to build the same mapping while
            // decoding, so there is always a shared common knowledge between the
            // encoding and decoder, which is also important for "timing" aspects like
            // how to handle variable bit width code encoding.
            //
            // One obvious but very important consequence of the table system is there
            // is always a unique id (at most 12-bits) to map the runs.  'A' might be
            // 4, then 'AA' might be 10, 'AAA' 11, 'AAAA' 12, etc.  This relationship
            // can be used for an effecient lookup strategy for the code mapping.  We
            // need to know if a run has been seen before, and be able to map that run
            // to the output code.  Since we start with known unique ids (input bytes),
            // and then from those build more unique ids (table entries), we can
            // continue this chain (almost like a linked list) to always have small
            // integer values that represent the current byte chains in the encoder.
            // This means instead of tracking the input bytes (AAAABCD) to know our
            // current state, we can track the table entry for AAAABC (it is guaranteed
            // to exist by the nature of the algorithm) and the next character D.
            // Therefor the tuple of (table_entry, byte) is guaranteed to also be
            // unique.  This allows us to create a simple lookup key for mapping input
            // sequences to codes (table indices) without having to store or search
            // any of the code sequences.  So if 'AAAA' has a table entry of 12, the
            // tuple of ('AAAA', K) for any input byte K will be unique, and can be our
            // key.  This leads to a integer value at most 20-bits, which can always
            // fit in an SMI value and be used as a fast sparse array / object key.

            // Output code for the current contents of the index buffer.
            var ibCode = indexStream[0] & codeMask; // Load first input index.
            var codeTable = {}; // Key'd on our 20-bit "tuple".

            emitCode(clearCode);    // Spec says first code should be a clear code.

            // First index already loaded, process the rest of the stream.
            for (var i = 1, il = indexStream.length; i < il; ++i) {
                var k = indexStream[i] & codeMask;
                var curKey = ibCode << 8 | k;   // (prev, k) unique tuple.
                var curCode = codeTable[curKey];    // buffer + k.

                // Check if we have to create a new code table entry.
                if (curCode === undefined) { // We don't have buffer + k.
                    // Emit index buffer (without k).
                    // This is an inline version of emit_code, because this is the core
                    // writing routine of the compressor (and V8 cannot inline emit_code
                    // because it is a closure here in a different context).  Additionally
                    // we can call emit_byte_to_buffer less often, because we can have
                    // 30-bits (from our 31-bit signed SMI), and we know our codes will only
                    // be 12-bits, so can safely have 18-bits there without overflow.
                    // emit_code(ib_code);
                    cur |= ibCode << curShift;
                    curShift += curCodeSize;
                    while (curShift >= 8) {
                        buf[p++] = cur & 0xFF;
                        cur >>= 8;
                        curShift -= 8;
                        if (p === curSubBlock + 256) {  // Finished a subblock.
                            buf[curSubBlock] = 255;
                            curSubBlock = p++;
                        }
                    }

                    if (nextCode === 4096) {    // Table full, need a clear.
                        emitCode(clearCode);
                        nextCode = eoiCode + 1;
                        curCodeSize = minCodeSize + 1;
                        codeTable = {};
                    } else {    // Table not full, insert a new entry.
                        // Increase our variable bit code sizes if necessary.  This is a bit
                        // tricky as it is based on "timing" between the encoding and
                        // decoder.  From the encoders perspective this should happen after
                        // we've already emitted the index buffer and are about to create the
                        // first table entry that would overflow our current code bit size.
                        if (nextCode >= (1 << curCodeSize))
                            ++curCodeSize;
                        codeTable[curKey] = nextCode++; // Insert into code table.
                    }

                    ibCode = k; // Index buffer to single input k.
                } else {
                    ibCode = curCode;   // Index buffer to sequence in code table.
                }
            }

            emitCode(ibCode);   // There will still be something in the index buffer.
            emitCode(eoiCode);  // End Of Information.

            // Flush / finalize the sub-blocks stream to the buffer.
            emitBytesToBuffer(1);

            // Finish the sub-blocks, writing out any unfinished lengths and
            // terminating with a sub-block of length 0.  If we have already started
            // but not yet used a sub-block it can just become the terminator.
            if (curSubBlock + 1 === p) {    // Started but unused.
                buf[curSubBlock] = 0;
            } else {    // Started and used, write length and additional terminator block.
                buf[curSubBlock] = p - curSubBlock - 1;
                buf[p++] = 0;
            }

            return p;
        }
    }
});