#ifndef __BIT_MASK32_H__
#define __BIT_MASK32_H__


#include <stdint.h>
#include <cassert>

#include "IStringConvertable.h"



class BitMask32 : public IStringConvertable {
public:
	BitMask32()
		: _mask(0) {}
	explicit BitMask32(uint32_t value)
		: _mask(value) {}
	virtual ~BitMask32() {}

private:
	uint32_t _mask;

protected:
	// impl. - IStringConvertable
	virtual void makeAsString(std::string& buf) const;

public:
	bool operator==(const BitMask32& mask) const;
	bool operator<(const BitMask32& mask) const;
	bool operator>(const BitMask32& mask) const;
	bool operator<=(const BitMask32& mask) const;
	bool operator>=(const BitMask32& mask) const;

	BitMask32& operator=(const BitMask32& mask);	
	BitMask32& operator&=(const BitMask32& mask);
	BitMask32& operator|=(const BitMask32& mask);
	BitMask32& operator-=(const BitMask32& mask);
	BitMask32& operator^=(const BitMask32& mask);

	friend BitMask32 operator& (const BitMask32& m1, const BitMask32& m2);
	friend BitMask32 operator| (const BitMask32& m1, const BitMask32& m2);
	friend BitMask32 operator- (const BitMask32& m1, const BitMask32& m2);
	friend BitMask32 operator^ (const BitMask32& m1, const BitMask32& m2);
	friend BitMask32 operator~ (const BitMask32& m1);

	void set(int32_t index);
	void set(const BitMask32& mask);
	void reset(int32_t index);
	void reset(const BitMask32& mask);
	void setAll() {	_mask = 0xFFFFFFFF;	}
	void resetAll() {	_mask = 0;	}

	bool isSet(int32_t index) const;
	bool isSetAll() const	{	return _mask == 0xFFFFFFFF;	}
	bool isSetAll(const BitMask32& mask) const;
	bool isSetAny(const BitMask32& mask) const;
	bool isEmpty() const {	return _mask == 0;	}
	int32_t length() const {	return 4;	}

	uint32_t asUint32() const {	return _mask;	}

	int32_t setCount() const;
};


// impl. - IStringConvertable
inline void BitMask32::makeAsString(std::string& buf) const
{
	char str[2 + 32 + 1];
	str[0] = '0';
	str[1] = 'b';
	for (int32_t i = 0; i < 32; i++) {
//		str[32 - 1 - i + 2] = isSet(i) ? '1' : '0';
		str[32 - 1 - i + 2] = isSet(i) ? '1' : '_';
	}
	str[2 + 32] = '\0';

	buf = str;
}

inline bool BitMask32::operator==(const BitMask32& mask) const
{
	return _mask == mask._mask;
}

inline bool BitMask32::operator<(const BitMask32& mask) const
{
	return _mask < mask._mask;
}

inline bool BitMask32::operator>(const BitMask32& mask) const
{
	return _mask > mask._mask;
}

inline bool BitMask32::operator<=(const BitMask32& mask) const
{
	return operator<(mask) || this->operator==(mask);
}

inline bool BitMask32::operator>=(const BitMask32& mask) const
{
	return operator>(mask) || this->operator==(mask);
}

inline BitMask32& BitMask32::operator=(const BitMask32& mask)
{
	_mask = mask._mask;
	return *this;
}

inline BitMask32& BitMask32::operator&=(const BitMask32& mask)
{
	_mask &= mask._mask;
	return *this;
}

inline BitMask32& BitMask32::operator|=(const BitMask32& mask)
{
	_mask |= mask._mask;
	return *this;
}

inline BitMask32& BitMask32::operator-=(const BitMask32& mask)
{
	_mask &= ~mask._mask;
	return *this;
}

inline BitMask32& BitMask32::operator^=(const BitMask32& mask)
{
	_mask ^= mask._mask;
	return *this;
}

inline BitMask32 operator&(const BitMask32& m1, const BitMask32& m2)
{
	return BitMask32(m1._mask & m2._mask);
}

inline BitMask32 operator|(const BitMask32& m1, const BitMask32& m2)
{
	return BitMask32(m1._mask | m2._mask);
}

inline BitMask32 operator-(const BitMask32& m1, const BitMask32& m2)
{
	return BitMask32(m1._mask & (~m2._mask));
}

inline BitMask32 operator^(const BitMask32& m1, const BitMask32& m2)
{
	return BitMask32(m1._mask ^ m2._mask);
}

inline BitMask32 operator~(const BitMask32& m1)
{
	return BitMask32(~m1._mask);
}

inline void BitMask32::set(int32_t index)
{
	assert(0 <= index && index < 32);
	_mask |= 0x01 << index;
}

inline void BitMask32::set(const BitMask32& mask)
{
	_mask |= mask._mask;
}

inline void BitMask32::reset(int32_t index)
{
	assert(0 <= index && index < 32);
	_mask &= ~(0x01 << index);
}

inline void BitMask32::reset(const BitMask32& mask)
{
	_mask &= ~mask._mask;
}

inline bool BitMask32::isSet(int32_t index) const
{
	assert(0 <= index && index < 32);
	return (_mask & (0x01 << index)) != 0;
}

inline bool BitMask32::isSetAll(const BitMask32& mask) const
{
	return (_mask & mask._mask) == mask._mask;
}

inline bool BitMask32::isSetAny(const BitMask32& mask) const
{
	return mask.isEmpty() || (_mask & mask._mask) != 0;
}

inline int32_t BitMask32::setCount() const
{
	int32_t result = 0;

	uint32_t mask = 0x01;
	for (int32_t i = 0; i < 32; i++) {
		if (_mask & mask) {
			result++;
		}
		mask <<= 1;
	}

	return result;
}

#endif	// __BIT_MASK32_H__
